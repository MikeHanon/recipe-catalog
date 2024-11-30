<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
Class UserService
{
    private $em;
    private $passwordHasher;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    public function registerUser(array $userData): void
    {
        $user = new User();
        $user->setEmail($userData['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $userData['password']));
        $this->em->persist($user);
        $this->em->flush();
    }

    public function authenticateUser(array $userData): ?User
    {
        $user = $this->getUserByEmail($userData['email']);
        if (null === $user) {
            return null;
        }
        if ($this->passwordHasher->isPasswordValid($user, $userData['password'])) {
            return $user;
        }
        return null;
    }

    protected function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }
}