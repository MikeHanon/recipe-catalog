<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\TokenService;
use App\Services\UserService;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(Request $request, TokenService $tokenService, UserService $userService): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $user = $userService->authenticateUser($userData);
        
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentitial',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $JwtToken = $tokenService->getJwtToken($user);
        $refreshToken = $tokenService->getRefreshToken($user); 
        return $this->json([
            'JwtToken' => $JwtToken,
            'refreshToken' => $refreshToken,
            
        ], JsonResponse::HTTP_OK);
    }
}
