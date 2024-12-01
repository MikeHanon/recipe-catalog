<?php

namespace App\Exception;

use Exception;

Class RecipeException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}