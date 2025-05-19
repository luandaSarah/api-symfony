<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Route('api/admin/users', 'api_admin_users_')] //Toute les routes commenceront par ca 
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->userRepository->findAll(),
            Response::HTTP_OK,

            //ici on defini des groupes afin de permettre la serialisation en JSON sur cette route specifique seulement sur les propriété qui sont accessible par les groupes (voir l'entité User)
            context: [
                'groups' => ['common:index','users:index']
            ]
        );
    }
}
