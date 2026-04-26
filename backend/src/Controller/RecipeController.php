<?php

namespace App\Controller;

use App\Service\RecipeRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController
{
    #[Route('/api/', name: 'api_home', methods: ['GET'])]
    public function apiHome(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'API publique Symfony opérationnelle.',
            'availableRoutes' => [
                'GET /api/recipes',
                'POST /admin/recipes',
            ],
        ]);
    }

    #[Route('/api/recipes', name: 'recipes_index', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): JsonResponse
    {
        return new JsonResponse([
            'items' => $recipeRepository->getAll(),
        ]);
    }

    #[Route('/admin/', name: 'admin_home', methods: ['GET'])]
    public function adminHome(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'API admin Symfony opérationnelle.',
            'usage' => 'Envoyer un POST JSON sur /admin/recipes pour créer une recette.',
        ]);
    }

    #[Route('/admin/recipes', name: 'recipes_create', methods: ['POST'])]
    public function create(Request $request, RecipeRepository $recipeRepository): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse([
                'message' => 'Le corps de la requête doit être un JSON valide.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $recipe = $recipeRepository->create($payload);
        } catch (RuntimeException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($recipe, JsonResponse::HTTP_CREATED);
    }

    #[Route('/health', name: 'recipes_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
