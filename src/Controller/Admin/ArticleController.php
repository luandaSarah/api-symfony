<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Annotation\Groups;

//Endpoint principales
#[Route('api/admin/articles', 'api_admin_articles_')]
class ArticleController extends AbstractController
{

    public function __construct(
        private ArticleRepository $articleRepository, //pour les requêtes haut niveau(lecture)
    ) {}

    /**
     * Methode GET 
     * Récupere tout les Articles
     *
     * @return JsonResponse
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->articleRepository->findAll(),
            Response::HTTP_OK,
            context: [
                'groups' => ['articles:index'],
            ]
        );
    }
}
