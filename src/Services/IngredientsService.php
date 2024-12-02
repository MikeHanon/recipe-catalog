<?php

namespace App\Services;

use App\Entity\Ingredients;
use App\Repository\IngredientsRepository;

Class IngredientsService
{
    private IngredientsRepository $ingredientsRepository;

    public function __construct(IngredientsRepository $ingredientsRepository)
    {
        $this->ingredientsRepository = $ingredientsRepository;
    }

    public function getIngredientsList(): array
    {
        $ingredients = $this->ingredientsRepository->findAll();
        $ingredientsList = [];

        foreach ($ingredients as $ingredient) {
            $ingredientsList[] = $this->getIngredientData($ingredient);
        }
        return $ingredientsList;
    }

    public function getIngredientData(Ingredients $ingredient): array
    {
        return [
            'id' => $ingredient->getId(),
            'name' => $ingredient->getName(),
        ];
    }
}