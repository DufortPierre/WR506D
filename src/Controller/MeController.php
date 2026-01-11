<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me', name: 'get_current_user', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
class MeController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $userData = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname(),
            'roles' => $user->getRoles(),
        ];

        return new JsonResponse($userData);
    }
}