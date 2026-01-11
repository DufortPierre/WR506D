<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * This controller is used for JWT authentication.
 * The actual authentication is handled by json_login in security.yaml,
 * but we need a route for Symfony to match the request.
 */
class AuthController extends AbstractController
{
    #[Route('/auth', name: 'auth', methods: ['POST'])]
    public function auth(Request $request): Response
    {
        // This should never be reached because json_login intercepts the request
        // But we need this route for Symfony routing
        return new Response('Authentication endpoint', Response::HTTP_OK);
    }
}
