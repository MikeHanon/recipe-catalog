<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Exception\RecipeException;
use App\Services\RecipeService;
use App\Utils\Validator\JsonRecipeValidator;
use Exception;
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

    #[Route('/api/recipes', name: 'app_recipes_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getRecipesList(): JsonResponse
    {
        try {
            $recipes = $this->recipeService->getRecipesList();
            if (empty($recipes)) {
                return new JsonResponse(['message' => 'no recipes found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse($recipes, Response::HTTP_OK);
        } catch (Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    #[Route('/api/recipes/create', name: 'app_recipes_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        try {
            $this->jsonRecipeValidator->validateRecipeData($requestData);
            $requestData['user'] = $this->getUser();
            $recipe = $this->recipeService->createRecipe($requestData);

        } catch (RecipeException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'recipe created', 'recipe_id' => $recipe->getId()], Response::HTTP_CREATED);
    }

    #[Route('/api/recipes/{id}', name: 'app_recipes_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getRecipe(int $id): JsonResponse
    {
        $recipe = $this->recipeService->getRecipeToArray($id);
        if (empty($recipe)) {
            return new JsonResponse(['message' => 'recipe not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($recipe, Response::HTTP_OK);
    }

    #[Route('/api/recipes/{id}', name: 'app_recipes_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateRecipe(int $id, Request $request): JsonResponse
    {
        try {
            $recipe = $this->recipeService->getRecipe($id);
            if (!$recipe instanceof Recipe) {
                return new JsonResponse(['message' => 'recipe not found'], Response::HTTP_NOT_FOUND);
            }
            $requestData = json_decode($request->getContent(), true);
            $this->jsonRecipeValidator->validateUpdateRecipeData($requestData);
            $this->recipeService->updateRecipe($id, $requestData);

        } catch (RecipeException $e) {

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'recipe '.$id.' updated'], Response::HTTP_CREATED);
    }


    #[Route('/api/recipes/{id}', name: 'app_recipes_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteRecipe(int $id): JsonResponse
    {
        try {
            $recipe = $this->recipeService->getRecipe($id);
            if (!$recipe instanceof Recipe) {
                return new JsonResponse(['message' => 'recipe not found'], Response::HTTP_NOT_FOUND);
            }
            $this->recipeService->deleteRecipe($id);

        } catch (RecipeException $e) {

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'recipe '.$id.' deleted'], Response::HTTP_OK);
    }

    #[Route('/api/recipes/ingredient/{ingredientName}', name: 'app_recipes_filter', methods: ['GET'])]
    public function filterByIngredient(string $ingredientName): JsonResponse
    {
        
        $recipes = $this->recipeService->filterByIngredientName($ingredientName);
        if (empty($recipes)) {
            return new JsonResponse(['message' => 'no recipes found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($recipes, Response::HTTP_OK);
    }
    
}
