<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Welcome to WR506D API',
            'version' => '1.0.0',
            'documentation' => '/api/docs',
            'api_base' => '/api',
            'endpoints' => [
                'movies' => '/api/movies',
                'actors' => '/api/actors',
                'categories' => '/api/categories',
                'directors' => '/api/directors',
                'users' => '/api/users',
                'api_keys' => '/api/api-keys',
                '2fa' => '/api/2fa',
                'serializer_demo' => '/api/serializer-demo',
            ],
        ], Response::HTTP_OK);
    }
}
