<?php

namespace App\Utils\Validator;

use App\Exception\RecipeException;

Class JsonRecipeValidator
{
    private const REQUIRED_FIELDS_RECIPE = ['name', 'instructions', 'ingredients'];
    private const REQUIRED_FIELDS_INGREDIENT = ['id', 'quantity', 'unit'];
    
    public function validateRecipeData(array $recipeData): bool
    {
        return  $this->validate($recipeData, self::REQUIRED_FIELDS_RECIPE) && $this->validateIngredientData($recipeData['ingredients']);
        
    }

    private function validateIngredientData(array $ingredientDatas): bool
    {
        $result = false;
        foreach ($ingredientDatas as $ingredientData) {
          $result =  $this->validate($ingredientData, self::REQUIRED_FIELDS_INGREDIENT);
        }
        
        return $result;
        
    }
    
    private function validate(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new RecipeException('missing field: ' . $field);
            }
        }
        
        return true;
    }
}