<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(Request $request, JWTTokenManagerInterface $jWTTokenManager, UserService $userService): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $user = $userService->authenticateUser($userData);
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentitial',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $token = $jWTTokenManager->create($user);
        return $this->json([
            'token' => $token,
            
        ], JsonResponse::HTTP_OK);
    }
}
