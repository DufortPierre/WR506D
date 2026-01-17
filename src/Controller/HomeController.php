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
        // Les URLs seront automatiquement préfixées par /WR506 grâce à la config routing.yaml
        $baseUrl = $this->generateUrl('app_home');
        $apiBase = str_replace('/', '/api', $baseUrl);
        
        return new JsonResponse([
            'message' => 'Welcome to WR506D API',
            'version' => '1.0.0',
            'documentation' => $apiBase . '/docs',
            'api_base' => $apiBase,
            'endpoints' => [
                'movies' => $apiBase . '/movies',
                'actors' => $apiBase . '/actors',
                'categories' => $apiBase . '/categories',
                'directors' => $apiBase . '/directors',
                'users' => $apiBase . '/users',
                'api_keys' => $apiBase . '/api-keys',
                '2fa' => $apiBase . '/2fa',
                'serializer_demo' => $apiBase . '/serializer-demo',
            ],
        ], Response::HTTP_OK);
    }
}
