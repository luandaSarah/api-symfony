<?php

namespace App\Controller;

use App\Mapper\UserMapper;
use App\Dto\User\RegisterUserDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    public function __construct(
        private readonly UserMapper $userMapper,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        // !WARNING: OLD CODE
        // $data = json_decode($request->getContent(), true);

        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     return $this->json(
        //         ['error' => 'Invalid JSON'],
        //         Response::HTTP_BAD_REQUEST
        //     );
        // }

        // $dto = new RegisterUserDto(
        //     $data['username'] ?? null,
        //     $data['firstName'] ?? null,
        //     $data['lastName'] ?? null,
        //     $data['plainPassword'] ?? null,
        //     $data['confirmPassword'] ?? null
        // );
        //!END OLD CODE 

        #[MapRequestPayload]
        //Attribut php 8 qui fonctionne quà partir de symfony 6.3, fait tout ce qu'il ya dans le OLD CODE 
        //il indique à symfony d'utiliser la deserialisation pour transformer le contenue requête en JSON en objet RegisterUserDto $dto   
        RegisterUserDto $dto
    ): JsonResponse {
        $user = $this->userMapper->map($dto);

        $this->em->persist($user);
        $this->em->flush();

        return $this->json(
            [
                'id' => $user->getId(),
            ],
            Response::HTTP_CREATED,
        );
    }
}
