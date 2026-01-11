<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Actor;
use App\Repository\MovieRepository;
use App\Repository\ActorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;

#[Route('/api/serializer-demo')]
class SerializerDemoController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private MovieRepository $movieRepository,
        private ActorRepository $actorRepository
    ) {
    }

    #[Route('/movies', name: 'serializer_demo_movies', methods: ['GET'])]
    public function getMovies(): JsonResponse
    {
        $movies = $this->movieRepository->findAll();
        
        // Configuration du contexte de sérialisation
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['movie:read'])
            ->withCircularReferenceHandler(function ($object) {
                return $object->getId();
            })
            ->toArray();
        
        $json = $this->serializer->serialize($movies, 'json', $context);
        
        return new JsonResponse(json_decode($json, true), Response::HTTP_OK, [], true);
    }

    #[Route('/movies/{id}', name: 'serializer_demo_movie', methods: ['GET'])]
    public function getMovie(int $id): JsonResponse
    {
        $movie = $this->movieRepository->find($id);
        
        if (!$movie) {
            return new JsonResponse(['error' => 'Movie not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Configuration avec profondeur maximale
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['movie:read'])
            ->withMaxDepth(3)
            ->withCircularReferenceHandler(function ($object) {
                return $object->getId();
            })
            ->toArray();
        
        $json = $this->serializer->serialize($movie, 'json', $context);
        
        return new JsonResponse(json_decode($json, true), Response::HTTP_OK, [], true);
    }

    #[Route('/actors', name: 'serializer_demo_actors', methods: ['GET'])]
    public function getActors(): JsonResponse
    {
        $actors = $this->actorRepository->findAll();
        
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['actor:read'])
            ->withCircularReferenceHandler(function ($object) {
                return $object->getId();
            })
            ->toArray();
        
        $json = $this->serializer->serialize($actors, 'json', $context);
        
        return new JsonResponse(json_decode($json, true), Response::HTTP_OK, [], true);
    }

    #[Route('/actors/{id}', name: 'serializer_demo_actor', methods: ['GET'])]
    public function getActor(int $id): JsonResponse
    {
        $actor = $this->actorRepository->find($id);
        
        if (!$actor) {
            return new JsonResponse(['error' => 'Actor not found'], Response::HTTP_NOT_FOUND);
        }
        
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['actor:read'])
            ->withMaxDepth(3)
            ->withCircularReferenceHandler(function ($object) {
                return $object->getId();
            })
            ->toArray();
        
        $json = $this->serializer->serialize($actor, 'json', $context);
        
        return new JsonResponse(json_decode($json, true), Response::HTTP_OK, [], true);
    }

    #[Route('/deserialize', name: 'serializer_demo_deserialize', methods: ['POST'])]
    public function deserializeMovie(Request $request): JsonResponse
    {
        $data = $request->getContent();
        
        try {
            // Désérialisation d'un objet Movie depuis JSON
            $movie = $this->serializer->deserialize(
                $data,
                Movie::class,
                'json',
                ['groups' => ['movie:write']]
            );
            
            return new JsonResponse([
                'message' => 'Movie deserialized successfully',
                'movie' => json_decode($this->serializer->serialize($movie, 'json', ['groups' => ['movie:read']]), true)
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Deserialization failed',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/config', name: 'serializer_demo_config', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        return new JsonResponse([
            'circular_reference_handler' => 'Enabled - Returns object ID',
            'max_depth' => 3,
            'skip_null_values' => true,
            'datetime_format' => 'Y-m-d H:i:s',
            'groups' => [
                'movie:read',
                'movie:write',
                'actor:read',
                'actor:write',
            ]
        ]);
    }
}
