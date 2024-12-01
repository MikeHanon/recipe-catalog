<?php

namespace App\Services;

use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Exception\RecipeException;
use App\Repository\IngredientsRepository;
use App\Repository\RecipeIngredientRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

Class RecipeService
{
    private RecipeRepository $recipeRepository;
    private RecipeIngredientRepository $recipeIngredientRepository;
    private IngredientsRepository $ingredientsRepository;
    private EntityManagerInterface $em;

    public function __construct(
        RecipeRepository $recipeRepository, 
        RecipeIngredientRepository $recipeIngredientRepository, 
        IngredientsRepository $ingredientsRepository,
        EntityManagerInterface $em,
    ){
        $this->recipeRepository = $recipeRepository;
        $this->recipeIngredientRepository = $recipeIngredientRepository;
        $this->ingredientsRepository = $ingredientsRepository; 
        $this->em = $em; 
    }

    public function getRecipesList(): array
    {
        $recipes = $this->recipeRepository->findAll();
        $recipesList = [];

        foreach ($recipes as $recipe) {
            $recipesList[] = $this->getRecipeData($recipe);
        }
        return $recipesList;
    }

    public function createRecipe(array $recipeData): void
    {
        $recipe = new Recipe();
        $recipe->setName($recipeData['name']);
        $recipe->setInstructions($recipeData['instructions']);
        $recipe->setCreatedAt(new \DateTimeImmutable());
        $recipe->setCreatedBy($recipeData['user']);

        

        foreach ($recipeData['ingredients'] as $ingredientData) {
            $ingredient = $this->ingredientsRepository->find($ingredientData['ingredient']);

            $recipeIngredient = new RecipeIngredient();
            $recipeIngredient->setRecipe($recipe);
            $recipeIngredient->setIngredient($ingredient);
            $recipeIngredient->setUnit($ingredientData['unit']);
            $recipeIngredient->setQuantity($ingredientData['quantity']);
            $this->em->persist($recipeIngredient);
            $recipe->addRecipeIngredient($recipeIngredient);
        }
        
        $this->em->persist($recipe);
        $this->em->flush();
    }

    public function getRecipeToArray(int $id): array
    {
        $recipe = $this->getRecipe($id);
        if (!$recipe instanceof Recipe) {
            throw new RecipeException('Recipe not found');
        }
        
        return $this->getRecipeData($recipe);
    }

    public function getRecipe(int $id): Recipe
    {
        $recipe =$this->recipeRepository->findOneBy(['id' => $id]);

        if (!$recipe instanceof Recipe) {
            throw new RecipeException('Recipe not found');
        }

        return $recipe;
    }

    private function getRecipeData(Recipe $recipe): array
    {
        $recipeData = [
            'id' => $recipe->getId(),
            'name' => $recipe->getName(),
            'instructions' => $recipe->getInstructions(),
            'ingredients' => $this->getIngredients($recipe->getRecipeIngredients()),
        ];

        
        return $recipeData;
    }

    private function getIngredients($recipeIngredients): array
    {
        $ingredients = [];
        foreach ($recipeIngredients as $ingredient) {
            $ingredients[] = [
                'id' => $ingredient->getIngredient()->getId(),
                'name' => $ingredient->getIngredient()->getName(),
                'quantity' => $ingredient->getQuantity(),
                'unit' => $ingredient->getUnit(),
            ];
        }
        return $ingredients;
    }

    public function updateRecipe(int $id, array $recipeData): void
    {
        $recipe = $this->getRecipe($id);

        foreach ($recipeData as $index => $data) {
            if ($index === 'ingredients') {
                $this->updateRecipeIngredientsList($recipe, $data);
                continue;
            }
            $methodName = 'set'.ucfirst($index);
            
            if (!method_exists($recipe, $methodName)) {
                
                throw new RecipeException('Invalid method: set'.$index);
            }
            $recipe->$methodName($data);
        }

        $this->em->persist($recipe);
        $this->em->flush();
    }

    private function updateRecipeIngredientsList(Recipe $recipe, array $ingredients): void
    {
        if (isset($ingredients['add'])) {
            $this->addRecipeIngredients($recipe, $ingredients['add']);
        } elseif (isset($ingredients['update'])) {
            $this->updateRecipeingredients($recipe, $ingredients['update']);
        } elseif (isset($ingredients['delete'])) {
            $this->deleteRecipeIngredients($recipe, $ingredients['delete']);
        }

        $this->em->persist($recipe);
        $this->em->flush();

    }

    private function addRecipeIngredients(Recipe $recipe, array $ingredientDatas): void
    {
        foreach ($ingredientDatas as $ingredientData) {
            $recipeIngredient = $this->addIngredient($recipe, $ingredientData);
            $recipe->addRecipeIngredient($recipeIngredient);

        }
        
    }

    private function addIngredient(Recipe $recipe, array $ingredientData): RecipeIngredient
    {
        $ingredient = $this->ingredientsRepository->find($ingredientData['ingredient']);
        if (!$ingredient) {
            throw new RecipeException('Ingredient not found with id: '.$ingredientData['ingredient']);
        }
        $recipeIngredient = new RecipeIngredient();
        $recipeIngredient->setRecipe($recipe);
        $recipeIngredient->setIngredient($ingredient);
        $this->setRecipeIngredientData($ingredientData, $recipeIngredient);
        $this->em->persist($recipeIngredient);

        return $recipeIngredient;
    }

    private function updateRecipeingredients(Recipe $recipe, array $ingredientDatas): void
    {
        foreach ($ingredientDatas as $ingredientData) {
           $recipeIngredient = $this->updateIngredient($recipe, $ingredientData);
           $recipe->updateRecipeIngredient($recipeIngredient);
        }
    }

    private function updateIngredient(Recipe $recipe, array $ingredientData): RecipeIngredient
    {
        $ingredient = $this->ingredientsRepository->find($ingredientData['id']);
        if (!$ingredient) {
            throw new RecipeException('Ingredient not found with id: '.$ingredientData['id']);
        }
        $recipeIngredient = $this->recipeIngredientRepository->findOneBy(['recipe' => $recipe, 'ingredient' => $ingredient]);
        if (!$recipeIngredient) {
            throw new RecipeException('RecipeIngredient not found');
        }
        $this->setRecipeIngredientData($ingredientData, $recipeIngredient);
        $this->em->persist($recipeIngredient);

        return $recipeIngredient;
    }

    private function deleteRecipeIngredients(Recipe $recipe, array $ingredientDatas): void
    {
        foreach ($ingredientDatas as $ingredientData) {
            $recipeIngredient = $this->deleteIngredient($recipe, $ingredientData);
            $recipe->removeRecipeIngredient($recipeIngredient);
        }
    }
    
    private function deleteIngredient(Recipe $recipe, array $ingredientData): RecipeIngredient
    {
        $ingredient = $this->ingredientsRepository->find($ingredientData['ingredient']);
        if (!$ingredient) {
            throw new RecipeException('Ingredient not found with id: '.$ingredientData['ingredient']);
        }
        $recipeIngredient = $this->recipeIngredientRepository->findOneBy(['recipe' => $recipe, 'ingredient' => $ingredient]);
        if (!$recipeIngredient) {
            throw new RecipeException('RecipeIngredient not found');
        }
        $this->em->remove($recipeIngredient);

        return $recipeIngredient;
    }
    private function setRecipeIngredientData(array $ingredientDatas, RecipeIngredient $recipeIngredient):void
    {
        if (isset($ingredientDatas['ingredient'])) {
            $ingredient = $this->ingredientsRepository->find($ingredientDatas['ingredient']);
            if (!$ingredient) {
                throw new RecipeException('Ingredient not found with id: '.$ingredientDatas['id']);
            }
            $recipeIngredient->setIngredient($ingredient);
        }

        if (isset($ingredientDatas['quantity'])) {
            $recipeIngredient->setQuantity($ingredientDatas['quantity']);
        }

        if (isset($ingredientDatas['unit'])) {
            $recipeIngredient->setUnit($ingredientDatas['unit']);
        }
        
    }

    public function deleteRecipe(int $id): void
    {
        $recipe = $this->getRecipe($id);
        $this->em->remove($recipe);
        $this->em->flush();
    }

    public function filterByIngredientName(string $ingredient): array
    {
        if (str_contains($ingredient, '_')) {
            $ingredientNameExploded = explode('_', $ingredient);
            $ingredient = implode(' ', $ingredientNameExploded);
        }
        $recipes = $this->recipeRepository->getFilteredRecipeByIngredientsName($ingredient);
        $recipesList = [];

        foreach ($recipes as $recipe) {
            $recipesList[] = $this->getRecipeData($recipe);
        }
        return $recipesList;
    }
}