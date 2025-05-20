<?php

namespace App\Dto\User;

use App\Dto\Interfaces\UserRequestInterface;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert; //l'Alias permet de recuperer juste le namespace de la classe et donc d'eviter d'avoir des use à chaque class appeler


//Contrainte d'unicité en bdd avec l'username
#[UniqueEntity(
    fields: ['username'],
    entityClass: User::class,
    message: 'Cet username est déjà utilisé'
)]
class RegisterUserDto implements UserRequestInterface
{
    public function __construct(
        /**
         * readonly : signifie que la propriété ne peut être assignée qu’une seule fois, 
         * au moment de la construction de l’objet (dans le constructeur ).
         * Une fois la valeur définie, elle ne peut plus être modifiée. 
         */
        #[Assert\NotBlank(
            message: 'Le nom d\'utilisateur est requis',
        )]
        #[Assert\Length(
            min: 3, //on defini la longueur minimal de notre username
            max: 180, //on defini la longueur maximal de notre username
            minMessage: 'Le nom d\'utilisateur doit contenir au moins {{limit}} caractères',
            maxMessage: 'Le nom d\'utilisateur ne doit pas contenir plus de {{limit}} caractères',
        )]
        private readonly ?string $username = null,

        #[Assert\NotBlank(
            message: 'Le mot de passe est requis',
        )] //grace à Assert, on sait qu"on ira cherhcher la methode Notblank dans la bonne class, notre username ne peut etre vide
        #[Assert\Length(
            min: 6, //on defini la longueur minimal de notre username
            max: 4096, //on defini la longueur maximal de notre username
            minMessage: 'Le mot de passe doit contenir au moins {{limit}} caractères',
            maxMessage: 'Le mot de pass ne doit pas contenir plus de {{limit}} caractères',
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/',
            message: 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial'
        )]
        private readonly ?string $plainPassword = null,
        private readonly ?string $firstName = null,
        private readonly ?string $lastName = null,

        #[Assert\NotBlank(
            message: 'La confirmation du mot de passe est requise',
        )] //grace à Assert, on sait qu"on ira cherhcher la methode Notblank dans la bonne class, notre username ne peut etre vide
        #[Assert\EqualTo(
            propertyPath: 'plainPassword',
            message: 'La confirmation du mot de passe doit correspndre au mot de passe',
        )]
        private readonly ?string $confirmPassword = null,

    ) {}

    /**
     * Get the value of userName
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Get the value of firstName
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Get the value of plainPassword
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Get the value of confirmPassword
     */
    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }
}
