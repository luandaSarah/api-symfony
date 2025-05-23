<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\DateTimeTraits;
use App\Repository\ArticleRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Gedmo\Mapping\Annotation as Gedmo; //on importe le bundle Gedmo pour l'automatisation des slug;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_TITLE', fields: ['title'])] //name : nom de la contrainte unicité que l'on defini, field: le champ qui doit étre unique
#[ORM\HasLifecycleCallbacks] //on indique que cette classe utilise des methodes lifecyclecallback
class Article
{
    use DateTimeTraits;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['common:index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['articles:index', 'articles:show'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['articles:index', 'articles:show'])]
    #[Gedmo\Slug(fields: ['title'])] //Automatisation du slug
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['articles:show'])]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['articles:index', 'articles:show'])]
    private ?string $shortContent = null;

    #[ORM\Column]
    #[Groups(['articles:index', 'articles:show'])]
    private ?bool $enable = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['articles:index'])]

    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getShortContent(): ?string
    {
        return $this->shortContent;
    }

    public function setShortContent(string $shortContent): static
    {
        $this->shortContent = $shortContent;

        return $this;
    }

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

}