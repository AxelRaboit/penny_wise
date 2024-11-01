<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\Security\LoginAttemptRepository;
use DateMalformedStringException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:cleanup-login-attempts',
    description: 'Deletes old login attempts'
)]
class CleanupLoginAttemptsCommand extends Command
{
    public function __construct(private readonly LoginAttemptRepository $loginAttemptRepository)
    {
        parent::__construct();
    }

    /**
     * @throws DateMalformedStringException
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loginAttemptRepository->deleteOldAttempts();
        $output->writeln('Attempts deleted.');

        return Command::SUCCESS;
    }
}
