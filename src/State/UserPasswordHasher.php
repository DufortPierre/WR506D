<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof \App\Entity\User) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $reflection = new \ReflectionClass($data);
        if (!$reflection->hasProperty('plainPassword')) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $plainPasswordProperty = $reflection->getProperty('plainPassword');
        $plainPasswordProperty->setAccessible(true);
        $plainPassword = $plainPasswordProperty->getValue($data);

        if (null !== $plainPassword && '' !== $plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $data,
                $plainPassword
            );
            $data->setPassword($hashedPassword);
            $plainPasswordProperty->setValue($data, null);
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}