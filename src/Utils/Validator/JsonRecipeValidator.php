<?php

namespace App\Utils\Validator;

use App\Exception\RecipeException;

Class JsonRecipeValidator
{
    private const REQUIRED_FIELDS_RECIPE = ['name', 'instructions', 'ingredients'];
    private const REQUIRED_FIELDS_RECIPE_INGREDIENT = ['ingredient', 'quantity', 'unit'];
    
    public function validateRecipeData(array $recipeData): bool
    {
        return  $this->validate($recipeData, self::REQUIRED_FIELDS_RECIPE) && $this->validateIngredientData($recipeData['ingredients']);
        
    }

    private function validateIngredientData(array $ingredientDatas): bool
    {
        $result = false;
        foreach ($ingredientDatas as $ingredientData) {
          $result =  $this->validate($ingredientData, self::REQUIRED_FIELDS_RECIPE_INGREDIENT);
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

    public function validateUpdateRecipeData(array $recipeData): bool
    {
        return  $this->validateUpdatable($recipeData, self::REQUIRED_FIELDS_RECIPE) && $this->validateUpdatableIngredientData($recipeData['ingredients']);
    }

    private function validateUpdatableIngredientData(array $ingredientDatas): bool
    {
        $result = false;

        if (array_key_exists('add', $ingredientDatas)) {
            foreach ($ingredientDatas['add'] as $ingredientData) {
                $result =  $this->validateUpdatable($ingredientData, self::REQUIRED_FIELDS_RECIPE_INGREDIENT);
              }
        } elseif (array_key_exists('update', $ingredientDatas)) {
            foreach ($ingredientDatas['update'] as $ingredientData) {
                $result =  $this->validateUpdatable($ingredientData, self::REQUIRED_FIELDS_RECIPE_INGREDIENT);
              }
        } elseif (array_key_exists('delete', $ingredientDatas)) {
            foreach ($ingredientDatas['delete'] as $ingredientData) {
                $result =  $this->validateUpdatable($ingredientData, self::REQUIRED_FIELDS_RECIPE_INGREDIENT);
              }
        }
        
        
        return $result;
        
    }

    private function validateUpdatable(array $datas, array $requiredFields): bool
    {
        foreach ($datas as $index => $data) {
            if (!in_array($index, $requiredFields)) {
                throw new RecipeException('field: ' . $index.'is not in field list. Updatable fields are: '.implode(', ', $requiredFields).' for data : '.key($datas));
            }
        }               
        
        return true;
    }
}