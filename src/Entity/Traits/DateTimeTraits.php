<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM; 

//type trait
trait DateTimeTraits
{
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    #[ORM\PrePersist]  //permet d'inserer la date de creation auto avant la persistance en bdd
    public function autoSetCreatedAt(): static 
    {
        if(!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }

        return $this;
    }


    #[ORM\PreUpdate]  //permet d'inserer la date de maj auto avant la modification en bdd
    public function autoSetUpdatedAt(): static 
    {
        if(!$this->updatedAt) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
}