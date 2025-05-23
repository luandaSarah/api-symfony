<?php

namespace App\Mapper;

use App\Dto\Interfaces\UserRequestInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserMapper
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function map(UserRequestInterface $dto, ?User $user = null): User
    {
        //si user n'est pas une entité User, alors on lui passe un objet User
        $user ??= new User;

        //si mon objet est null ou ne contient pas la propriété username qui correspond à celeui que j'ai dans on dto
        if (null !== $dto->getUsername()) {
            //on met dans la propriété username de notre nvl objt user, ce qu'il ya dans mon dto
            $user->setUsername(
                $dto->getUsername()
            );
        }

        if (null !== $dto->getFirstName()) {
            $user->setFirstName(
                $dto->getFirstName()
            );
        }

        if (null !== $dto->getLastName()) {
            $user->setLastName(
                $dto->getLastName()
            );
        }


        if (null !== $dto->getPlainPassword()) {
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $dto->getPlainPassword()
                )
            );
        }
        return $user;
    }
}
