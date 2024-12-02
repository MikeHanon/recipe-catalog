<?php

namespace App\Controller;

use App\Services\IngredientsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class IngredientsController extends AbstractController
{
    private IngredientsService $ingredientsService;

    public function __construct(IngredientsService $ingredientsService)
    {
        $this->ingredientsService = $ingredientsService;
    }


    #[Route('/api/ingredients', name: 'app_ingredients', methods: ['GET'])]
    public function getIngredients(): JsonResponse
    {
        $ingredients = $this->ingredientsService->getIngredientsList();

        if (empty($ingredients)) {
            return new JsonResponse(['message' => 'No ingredients found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($ingredients, JsonResponse::HTTP_OK);
    }
}
