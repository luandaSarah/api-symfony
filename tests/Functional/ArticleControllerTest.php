<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Entity\Article;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ArticleControllerTest extends WebTestCase //webTesCase permet de faire des tests fonctionnels
{

  //Propriété pour stocker le client léger (pour envoyer des requetes)
  private KernelBrowser $client;

  private AbstractDatabaseTool $databaseTool; // Permet de gérer les fixtures et la base de données, il provient de LiipTestFixturesBundle

  protected function setUp(): void
  {
    //création du client léger pour les tests
    $this->client = self::createClient(
      server: [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
      ]
    );
    $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get(); // Récupère l'outil de gestion de la base de données


  }

  private function getUser(string $username = 'admin'): ?User
  {
    // On load les fixtures
    $this->databaseTool->loadAliceFixture([
      __DIR__ . '/Fixtures/UserFixtures.yaml'
    ]);

    // On récupère l'utilisateur par son nom d'utilisateur
    $user = self::getContainer()->get(UserRepository::class)
      ->findOneBy(['username' => $username]);

    // On le renvois
    return $user;
  }

  public function testIndexEndPointWithNoConnectedUser(): void
  {
    $this->client->request('GET', '/api/admin/articles');

    $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
  }

  /**
   * Cette méthode teste l'endpoint /api/admin/articles
   * avec un utilisateur connecté qui n'est pas un administrateur.
   * @return void
   */
  public function testIndexEndPointWithConnectedUser(): void
  {

    //On connecte l'utilisateur
    $this->client->loginUser($this->getUser('user'), 'login');

    // On envoie une requête GET à l'endpoint /api/admin/articles
    $this->client->request('GET', '/api/admin/articles');

    // On vérifie que la réponse a le code HTTP 403 (Forbidden)
    $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
  }


  /**
   * Cette méthode teste l'endpoint /api/admin/articles
   * avec un utilisateur connecté qui n'est pas un administrateur.
   * @return void
   */
  public function testIndexEndPointWithConnectedAdmin(): void
  {

    //On connecte l'utilisateur
    $this->client->loginUser($this->getUser('admin'), 'login');

    // On envoie une requête GET à l'endpoint /api/admin/articles
    $this->client->request('GET', '/api/admin/articles');

    // On vérifie que la réponse a le code HTTP 403 (Forbidden)
    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }

  /**
   * Cette méthode teste si la réponse recu renvoie la bonne structure JSON
   * pour l'endpoint /api/admin/articles.
   * @return void
   */
  public function testIndexEndPointValidateStructureJsonResponse(): void
  {

    // On connecte l'utilisateur
    $this->client->loginUser($this->getUser('admin'), 'login');

    // On envoie une requête GET à l'endpoint /api/admin/articles
    $this->client->request('GET', '/api/admin/articles');

    //On recupere le contenu de la réponse et on le décode en tableau associatif
    $response = json_decode($this->client->getResponse()->getContent(), true);

    // On vérifie que la réponse est un tableau associatif
    $this->assertIsArray($response,);

    // On vérifie que la réponse contient les clés attendues
    $this->assertArrayHasKey('items', $response);
    $this->assertArrayHasKey('meta', $response);
    $this->assertArrayHasKey('pages', $response['meta']);
    $this->assertArrayHasKey('total', $response['meta']);
  }

  /**
   * Cette méthode teste si la réponse recu renvoie le bon nombre d'items
   * pour l'endpoint /api/admin/articles.
   * @return void
   */
  public function testIndexEndPointValidateNumberOfItemsDefault(): void
  {
    // On connecte l'utilisateur
    $this->client->loginUser($this->getUser(), 'login');

    // On charge les fixtures d'articles
    $this->databaseTool->loadAliceFixture([
      __DIR__ . '/Fixtures/ArticleFixtures.yaml'
    ]);

    // On envoie une requête GET à l'endpoint /api/admin/articles
    $this->client->request('GET', '/api/admin/articles');

    // On décode la réponse JSON en tableau associatif
    $response = json_decode($this->client->getResponse()->getContent(), true);


    // On vérifie que la réponse contient 10 items par défaut
    $this->assertCount(10, $response['items']);
  }

  /**
   * Cette méthode teste si la réponse recu renvoie le bon nombre d'items
   * pour l'endpoint /api/admin/articles avec un paramètre limit.
   * @return void
   */
  public function testIndexEndPointValidateNumberOfItemsWithLimitParameter(): void
  {
    // On connecte l'utilisateur
    $this->client->loginUser($this->getUser(), 'login');

    // On charge les fixtures d'articles
    $this->databaseTool->loadAliceFixture([
      __DIR__ . '/Fixtures/ArticleFixtures.yaml'
    ]);

    // On envoie une requête GET à l'endpoint /api/admin/articles avec un paramètre limit à 1 item
    $this->client->request('GET', '/api/admin/articles?limit=1');

    // On décode la réponse JSON en tableau associatif
    $response = json_decode($this->client->getResponse()->getContent(), true);

    // On vérifie que la réponse contient bien 1 item
    $this->assertCount(1, $response['items']);

    // On vérifie que la réponse contient 12 pages au total
    $this->assertEquals(12, $response['meta']['pages']);
  }

  public function testIndexEndPointValidateErrorWhenLimitIsNotPositive(): void
  {
    //recuperer l'user
    $this->client->loginUser($this->getUser(), 'login');


    //envoyer une requete avec un paramètre limit négatif et on fait atention à ce que la reponse soit bien en format JSON
    $this->client->request(
      'GET',
      '/api/admin/articles?limit=-1',
      server: [
        'HTTP_ACCEPT' => 'application/json',
      ]
    );

    //On Verifie si on recois bien une erreur 404
    $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

    // On décode la réponse JSON en tableau associatif
    $response = json_decode($this->client->getResponse()->getContent(), true);

    // On verifie que la reponse contient le message d'erreur attendu pour s'assuerer que la validation a bien fonctionné
    $this->assertEquals(
      "limit: This value should be positive.",
      $response['detail']
    );
  }

  public function testIndexEndPointValidateErrorWhenPageIsNotPositive(): void
  {
    //recuperer l'user
    $this->client->loginUser($this->getUser(), 'login');


    //envoyer une requete avec un paramètre limit négatif et on fait atention à ce que la reponse soit bien en format JSON
    $this->client->request(
      'GET',
      '/api/admin/articles?page=-1',
      server: [
        'HTTP_ACCEPT' => 'application/json',
      ]
    );

    //On Verifie si on recois bien une erreur 404
    $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

    // On décode la réponse JSON en tableau associatif
    $response = json_decode($this->client->getResponse()->getContent(), true);

    // On verifie que la reponse contient le message d'erreur attendu pour s'assuerer que la validation a bien fonctionné
    $this->assertEquals(
      "page: This value should be positive.",
      $response['detail']
    );
  }

  /**
   * Cette méthode teste si la réponse recu renvoie le bon premier item
   * pour l'endpoint /api/admin/articles avec un paramètre page.
   * @return void
   */
  public function testIndexEndPointValidateFirstItemWhenPageisChanged(): void
  {
    //On connecte l'utilisateur
    $this->client->loginUser($this->getUser(), 'login');

    // On charge les fixtures d'articles
    $this->databaseTool->loadAliceFixture([
      __DIR__ . '/Fixtures/ArticleFixtures.yaml'
    ]);

    // On envoie une requête GET à l'endpoint /api/admin/articles avec un paramètre page à 2 
    $this->client->request('GET', '/api/admin/articles?page=2');

    // On décode la réponse JSON en tableau associatif
    $response = json_decode($this->client->getResponse()->getContent(), true);

    // On vérifie que le premier item de la réponse est l'article attendu pour la page 2
    $this->assertEquals(
      'Article 11',
      $response['items'][0]['title']
    );
  }


  /**
   * cette methode verifie si l'utilisateur qui veut créer un article n'est pas un un simple utilisateur
   * et qu'il est bien un administrateur.
   * @return void
   */
  public function testCreateEndPointWithConnectedUser(): void
  {
    //on connecte l'utilisateur
    $this->client->loginUser($this->getUser('user'), 'login');

    //on envoie une requete POST à l'endpoint /api/admin/articles
    $this->client->request('POST', '/api/admin/articles');

    //on verifie que la reponse a le code HTTP 403 (Forbidden)
    $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
  }

  public function testCreateEndPointWithConnectedAdmin(): void
  {
    //on connecte l'utilisateur
    $this->client->loginUser($this->getUser('admin'), 'login');

    //on recupere l'utilisateur 
    $user = $this->getUser();

    //on envoie une requete POST à l'endpoint /api/admin/articles
    $this->client->request(
      'POST',
      '/api/admin/articles',
      [
        'title' => 'Article de test',
        'content' => 'Contenu de l\'article de test',
        'shortContent' => 'Contenu court de l\'article de test',
        'user' => $user->getId(),
      ]
    );

    //on verifie que la reponse a le code HTTP 201 (Created)
    $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
  }

  public function testCreateEndPointValidateCreationInBdd(): void
  {


    //on recupere l'utilisateur 
    $user = $this->getUser();

    //on connecte l'utilisateur
    $this->client->loginUser($user, 'login');


    //on envoie une requete POST à l'endpoint /api/admin/articles
    $this->client->request(
      'POST',
      '/api/admin/articles',
      [
        'title' => 'Article de test',
        'content' => 'Contenu de l\'article de test',
        'shortContent' => 'Contenu court de l\'article de test',
        'user' => $user->getId(),
      ]
    );

    //on verifie que l'article a bien été créé en base de données
    $article = self::getContainer()->get(ArticleRepository::class)->findOneBy(['title' => 'Article de test']);


    //On verifie que ca a bien créé une instance de notre entité Article
    $this->assertInstanceOf(Article::class, $article);
  }

  /**
   * Cette méthode teste si l'utlisateur qui veut modifier est connecté
   * @return void
   * 
   */
  public function testUpdateEndpointWithNoConnectedUser(): void
  {
    $this->client->request('PATCH', '/api/admin/articles/1');

    $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
  }

  /**
   * Cette méthode teste si l'utilisateur est connécté et si la modificaton se fait en bdd
   * @return void
   */
  public function testUpdateEndpointWithConnectedUser(): void
  {
    // On connecte d'abord l'utilisateur
    $this->client->loginUser(
      $this->getUser('user'),
      'login'
    );

    $this->client->request('PATCH', '/api/admin/articles/1');
    $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
  }

  /**
   * Cette méthode teste si l'utilisateur qui veut modifier un article n'est pas un simple utilisateur
   * et qu'il est bien un administrateur.
   * @return void
   */
  public function testUpdateEndpointWithConnectedAdmin(): void
  {
    $this->client->loginUser(
      $this->getUser(),
      'login'
    );

    $this->databaseTool->loadAliceFixture([
      __DIR__ . '/Fixtures/ArticleFixtures.yaml'
    ]);

    $article = self::getContainer()->get(ArticleRepository::class)->findOneBy(['title' => 'Article 1']);

    $this->client->request('PATCH', "/api/admin/articles/{$article->getId()}", [
      'title' => 'Article modifié',
    ]);

    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }
}
