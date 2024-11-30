<?php

namespace App\Controller;

use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $resquest, UserService $userService): JsonResponse
    {
        $userData = json_decode($resquest->getContent(), true);
        if (empty($userData['email']) || empty($userData['password'])) {
            return new JsonResponse(['status' => 'Missing required data.'], Response::HTTP_BAD_REQUEST);
        }
        $userService->registerUser($userData);
        return new JsonResponse(['status' => 'User created!'], 201);
    }
}
