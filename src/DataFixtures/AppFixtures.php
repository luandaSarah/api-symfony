<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture

{

    private $faker;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher //objet qui permet la 
    ) {

        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new User();
        $user
            ->setUsername('jojodoe')
            ->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    '123mdp'
                )
            )
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);


        for ($i = 0; $i <= 15; $i++) {
            $article = new Article();
            $article
                ->setTitle("article $i")
                ->setContent("C'est le contenu de l'article $i")
                ->setShortContent("Preview de $i")
                ->setEnable(true)
                ->setUSer($user);

            $manager->persist($article);
        }

        for ($i = 1; $i <= 15; $i++) {
            $user = new User;
            $user
                ->setUsername($this->faker->unique()->userName())
                ->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setPassword(
                    $this->passwordHasher->hashPassword(
                        $user,
                        '123mdp'
                    )
                );

            $manager->persist($user);
        }

        $manager->flush();
    }
}
