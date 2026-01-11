<?php

namespace App\Command;

use App\Entity\ApiKey;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:api-key:generate',
    description: 'Generate a new API key for a user',
)]
class GenerateApiKeyCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'The ID of the user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('userId');

        $user = $this->userRepository->find($userId);

        if (!$user) {
            $io->error("User with ID {$userId} not found.");
            return Command::FAILURE;
        }

        // Generate API key
        $plainApiKey = bin2hex(random_bytes(32)); // 64 hex characters
        $prefix = substr($plainApiKey, 0, 16);
        $salt = bin2hex(random_bytes(400)); // 800 hex chars = 400 bytes
        $hash = substr(hash('sha256', $plainApiKey . $salt), 0, 34);

        // Create ApiKey entity
        $apiKey = new ApiKey();
        $apiKey->setUser($user);
        $apiKey->setApiKeyPrefix($prefix);
        $apiKey->setApiKeySalt($salt);
        $apiKey->setApiKeyHash($hash);
        $apiKey->setIsActive(true);

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        $io->success('API Key generated successfully!');
        $io->writeln("API Key: {$plainApiKey}");
        $io->writeln("Prefix: {$prefix}");
        $io->writeln("User: {$user->getEmail()} (ID: {$user->getId()})");
        $io->warning('Store this API key securely. It will not be shown again.');

        return Command::SUCCESS;
    }
}
