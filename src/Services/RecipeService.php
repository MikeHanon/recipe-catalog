<?php

namespace App\Services;

use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
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


    public function createRecipe(array $recipeData): void
    {
        $recipe = new Recipe();
        $recipe->setName($recipeData['name']);
        $recipe->setInstructions($recipeData['instructions']);
        $recipe->setCreatedAt(new \DateTimeImmutable());
        $recipe->setCreatedBy($recipeData['user']);

        

        foreach ($recipeData['ingredients'] as $ingredientData) {
            $ingredient = $this->ingredientsRepository->find($ingredientData['id']);

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
}