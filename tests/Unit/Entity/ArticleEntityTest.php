<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ArticleEntityTest extends KernelTestCase
{

    private EntityManagerInterface $entityManager;

    private AbstractDatabaseTool $databaseTool; // Permet de gérer les fixtures et la base de données, il provient de LiipTestFixturesBundle

    public function setUp(): void //est executer avant chaque test
    {
        //Init le kernel Symfo
        self::bootKernel(); //Appartient à la class KernelTestCase

        //On récupère l'entityManager qu'on stock ds la propriété
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class); // cela permet de récupérer le service EntityManagerInterface depuis le conteneur de services de Symfony
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get(); // Récupère l'outil de gestion de la base de données

        $this->databaseTool->loadFixtures(); // Charge les fixtures avant chaque test
    }

    private function getUser(): User
    {
        return (new User)
            ->setUsername('Test')
            ->setPassword('password');
    }

    private function getArticle(): Article
    {
        return (new Article)
            ->setTitle('Titre de test')
            ->setContent('Contenu de l\'article de test')
            ->setShortContent('Contenu court de l\'article de test')
            ->setUser($this->getUser())
            ->SetEnable(true);
    }

    private function persistData(Article $article, User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->persist($article);

        $this->entityManager->flush();
    }

    public function testGenerationSlugByTitle(): void
    {

        $article = $this->getArticle();

        $this->persistData($article, $article->getUser());


        //cet assert permet de verifier si ce qu'on test correspond au résultat attendu 
        //prends en param: le resultat qu'il attends à la sortie du test, le test et le message à afficher (optionnel)
        $this->assertEquals('titre-de-test', $article->getSlug());
    }

    public function testGenerationCreatedAtOnPersist(): void
    {
        $article = $this->getArticle();

        $this->persistData($article, $article->getUser());

        $expected = (new \DateTimeImmutable())->format('Y-m-d H:i');

        $this->assertEquals($expected, $article->getCreatedAt()->format('Y-m-d H:i'));
    }

    public function testGenerationCreatedAtOnPersistWithExistingCreatedAt(): void
    {
        $createdAt = new \DateTimeImmutable('2025-01-01 12:00');

        $article = $this->getArticle()
            ->setCreatedAt($createdAt);

        $this->persistData($article, $article->getUser());
        $this->assertEquals($createdAt->format('Y-m-d H:i'), $article->getCreatedAt()->format('Y-m-d H:i'));
    }

    public function testGenerationUpdatedAtOnUpdate(): void
    {
        $article = $this->getArticle();

        $this->persistData($article, $article->getUser());

        $this->assertNull($article->getUpdatedAt());

        $article->setTitle('Nouveau titre');

        $this->entityManager->flush();

        $expected = (new \DateTimeImmutable())->format('Y-m-d H:i');

        $this->assertEquals($expected, $article->getUpdatedAt()->format('Y-m-d H:i'));
    }

    public function testExceptionWhenNoUniqueTitle(): void
    {
        $this->databaseTool->loadAliceFixture(
            [
                \dirname(__DIR__) . '/Fixtures/ArticleFixtures.yaml'
                ]
        );

        $article = $this->getArticle()
            ->setTitle('Article de test'); // Meme titre que celui utilisé dans les fixtures

        $this->expectException(UniqueConstraintViolationException::class);

        $this->persistData($article, $article->getUser());
    }
}
