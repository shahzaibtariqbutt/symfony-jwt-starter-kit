<?php

namespace App\Api\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Webmozart\Assert\Assert;

/**
 * @implements ProcessorInterface<User, User>
 */
readonly class UserRequestDeletionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        //        private MailerInterface $mailer,
    ) {
    }

    /**
     * @param array<mixed>              $uriVariables
     * @param array{request?: Request,} $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        Assert::isInstanceOf($user = $data, User::class);
        Assert::same($user->status, UserStatus::Active);

        $user->status = UserStatus::DeletionRequested;
        $user->deletionRequestedAt = new DatePoint();
        $this->em->flush();

        // todo and also assert in tests
        //        $email = new TemplatedEmail()
        //            ->to($user->getMimeAddress())
        //            ->subject('Your account has been queued for deletion')
        //            ->htmlTemplate('emails/deletion_requested.html.twig')
        //            ->context([
        //                'user' => $user,
        //            ]);
        //
        //        $this->mailer->send($email);

        return $user;
    }
}
