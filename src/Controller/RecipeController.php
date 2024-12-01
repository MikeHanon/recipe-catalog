<?php

namespace App\Controller;

use App\Exception\RecipeException;
use App\Services\RecipeService;
use App\Utils\Validator\JsonRecipeValidator;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecipeController extends AbstractController
{
    private RecipeService $recipeService;
    private JsonRecipeValidator $jsonRecipeValidator;

    public function __construct(RecipeService $recipeService, JsonRecipeValidator $jsonRecipeValidator)
    {
        $this->recipeService = $recipeService;
        $this->jsonRecipeValidator = $jsonRecipeValidator;
    }


    #[Route('/api/recipes/create', name: 'app_recipes_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        try {
            $this->jsonRecipeValidator->validateRecipeData($requestData);
            $requestData['user'] = $this->getUser();
            $this->recipeService->createRecipe($requestData);

        } catch (RecipeException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'recipe created'], Response::HTTP_CREATED);
    }
}
