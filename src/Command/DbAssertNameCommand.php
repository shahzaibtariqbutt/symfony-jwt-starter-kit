<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:db:assert-name',
    description: 'For safety in a script, confirm database name is as expected'
)]
class DbAssertNameCommand extends Command
{
    public function __construct(
        #[Autowire('%env(DATABASE_URL)%')]
        private readonly string $databaseUrl,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('allowedNames', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $allowedNames = explode(',', $input->getArgument('allowedNames'));
        $actual = ltrim(parse_url($this->databaseUrl)['path'] ?? '', '/');

        if (!\in_array($actual, $allowedNames, true)) {
            $io->error("Database name $actual did not match allowed ".implode(',', $allowedNames));

            return Command::FAILURE;
        }

        $io->success("Database name $actual as expected");

        return Command::SUCCESS;
    }
}
