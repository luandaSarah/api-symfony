<?php

namespace App\Mapper;

use App\Entity\Article;
use App\Repository\UserRepository;
use App\Dto\Interfaces\ArticleRequestInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleMapper
{

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function map(ArticleRequestInterface $dto, ?Article $article = null): Article
    {
        $article ??= new article;

        if (null !== $dto->getTitle()) {
            $article->setTitle(
                $dto->getTitle()
            );
        };
        if (null !== $dto->getContent()) {
            $article->setContent(
                $dto->getContent()
            );
        };

        if (null !== $dto->getShortContent()) {
            $article->setShortContent(
                $dto->getShortContent()
            );
        };

        if (null !== $dto->isEnable()) {
            $article->setEnable(
                $dto->isEnable()

            );

            if (null !== $dto->getUser()) {

                //on recuperee l'user en bdd
                $user = $this->userRepository->find($dto->getUser());

                //si l'user n'existe pas en bdd on stop le processus et on envoie une erreur php
                if (null === $user) {
                    throw new NotFoundHttpException('User not found');
                }

                $article->setUser(
                    $user
                );
            };
        }
        return $article;
    }
}
