<?php

namespace App\Services;

use App\Entity\RefreshToken;
use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

Class TokenService
{
    private $jWTTokenManager;
    private $refreshTokenManager;

    public function __construct(JWTTokenManagerInterface $jWTTokenManager, RefreshTokenManagerInterface $refreshTokenManager) 
    {
        $this->jWTTokenManager = $jWTTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    public function getJwtToken(User $user): string
    {
        return $this->jWTTokenManager->create($user);
    }

    public function getRefreshToken(User $user): string
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUsername($user->getEmail());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid(new \DateTime('+1 month'));
        $this->refreshTokenManager->save($refreshToken);
        return $refreshToken->getRefreshToken();
    }
}