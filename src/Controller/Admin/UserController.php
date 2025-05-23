<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use App\Dto\User\UpdateUserByAdminDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Json;

#[Route('api/admin/users', 'api_admin_users_')] //Toute les routes commenceront par ca 
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private readonly UserMapper $userMapper, //les data ne peuvent etre modifier à ce moement là
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->userRepository->findAll(),
            Response::HTTP_OK,

            //ici on defini des groupes afin de permettre la serialisation en JSON sur cette route specifique seulement sur les propriété qui sont accessible par les groupes (voir l'entité User)
            context: [
                'groups' => ['common:index', 'users:index']
            ]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json(
            $user,
            Response::HTTP_OK,
            context:[
                'groups'=> ['common:index', 'users:index', 'users:show'],
            ]
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        User $user,
        #[MapRequestPayload]
        UpdateUserByAdminDto $dto,
    ): JsonResponse {

        $this->userMapper->map($dto, $user);

        $this->em->flush();

        return $this->json(
            $user,
            Response::HTTP_OK,
            context: [
                'groups' => ['common:index', 'users:index', 'users:show']
            ]
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->em->remove($user);
        $this->em->flush();

        return $this->json(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}
