<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'app:users:deletion:perform',
    description: 'Perform user deletions which have pending deletion request. User record remains, but all PII is anonymised'
)]
final class UserDeletionPerformCommand extends Command
{
    private const COOLING_OFF_PERIOD_DAYS = 14;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'no-confirmation-email',
            null,
            InputOption::VALUE_NONE,
            'Skip sending confirmation emails'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $skipEmail = (bool) $input->getOption('no-confirmation-email');

        $now = new DatePoint();
        $cutoffDate = $now->modify('-'.self::COOLING_OFF_PERIOD_DAYS.' days')
            ->setTime(23, 59, 59);
        $users = $this->findUsersForDeletion($cutoffDate);

        if (empty($users)) {
            $io->success('No users found for deletion.');

            return Command::SUCCESS;
        }

        $deletedCount = 0;
        foreach ($users as $user) {
            $io->writeln("Processing user ID: {$user->id}, Email: {$user->email}");

            $user->anonymise();
            $user->deletionPerformedAt = new \DateTimeImmutable();
            $this->em->flush();
            ++$deletedCount;

            if (!$skipEmail) {
                $this->sendDeletionConfirmationEmail($user);
            }
        }

        $this->em->clear();

        $io->success("Successfully processed {$deletedCount} user deletions.");

        return Command::SUCCESS;
    }

    private function sendDeletionConfirmationEmail(User $user): void
    {
        $email = new TemplatedEmail()
            ->to($user->getMimeAddress())
            ->subject('Your account has been deleted')
            ->htmlTemplate('emails/deletion_performed.html.twig')
            ->context(['name' => $user->name]);

        $this->mailer->send($email);
    }

    /**
     * Find users eligible for deletion (status = DeletionRequested, deletionRequestedAt older than cutoff, deletionPerformedAt is null).
     *
     * @return User[]
     */
    private function findUsersForDeletion(\DateTimeInterface $cutoffDate): array
    {
        return $this->em->createQuery(
            'SELECT u
                FROM '.User::class.' u
                WHERE u.status = :status
               AND u.deletionRequestedAt <= :cutoffDate
               AND u.deletionPerformedAt IS NULL'
        )
            ->setParameter('status', UserStatus::DeletionRequested)
            ->setParameter('cutoffDate', $cutoffDate)
            ->getResult();
    }
}
