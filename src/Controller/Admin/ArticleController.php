<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Mapper\ArticleMapper;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\Article\CreateArticleByAdminDto;
use App\Dto\Article\UpdateArticleByAdminDto;
use App\Dto\Filter\ArticleFilterDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

//Endpoint principales
#[Route('api/admin/articles', 'api_admin_articles_')]
class ArticleController extends AbstractController
{

    public function __construct(
        private ArticleRepository $articleRepository, //pour les requêtes haut niveau(lecture)
        private readonly EntityManagerInterface $em, //les data ne peuvent etre modifier à ce moement là 
        private readonly ArticleMapper $articleMapper,
    ) {}

    /**
     * Methode GET 
     * Récupere tout les Articles
     * @return JsonResponse
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        #[MapQueryString]
        ArticleFilterDto $articleFilterDto, //transforme tout ce qu'il ya en paramettre GET de l'url après le ?
    ): JsonResponse {
        return $this->json(
            $this->articleRepository->findPaginate($articleFilterDto),
            Response::HTTP_OK,
            context: [
                'groups' => ['articles:index', 'common:index'],
            ]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Article $article): JsonResponse
    {
        return $this->json(
            $article,
            Response::HTTP_OK,
            context: [
                'groups' => ['articles:show', 'articles:index', 'common:index']
            ]
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload]
        CreateArticleByAdminDto $dto
    ): JsonResponse {
        $article = $this->articleMapper->map($dto);

        $this->em->persist($article); //On créer en bdd
        $this->em->flush();

        return $this->json(
            [
                'id' => $article->getId(),
            ],
            Response::HTTP_CREATED,
            context: [
                'groups' => ['articles:show'],
            ]
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        Article $article,
        #[MapRequestPayload]
        UpdateArticleByAdminDto $dto,

    ): JsonResponse {

        $this->articleMapper->map($dto, $article);
        $this->em->flush();

        return $this->json(
            $article,
            Response::HTTP_OK,
            context: [
                'groups' => ['articles:show', 'common:index']
            ]
        );
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Article $article): JsonResponse
    {
        $this->em->remove($article);
        $this->em->flush();

        return $this->json(
            null, //on ne renvoie rien car on renvoie un statut http 204, no content
            Response::HTTP_NO_CONTENT
        );
    }
}
