<?php

namespace App\Entity;

use App\Repository\SpaceShipCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Contracts\Translation\TranslatorInterface;
#[ORM\Entity(repositoryClass: SpaceShipCategoryRepository::class)]
class SpaceShipCategory
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    public function __construct(
    private TranslatorInterface $translator,
  ) {
    $this->translator = $translator;
  }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    public function __toString(): string
    {
        
        return $this->name;
    }
}
