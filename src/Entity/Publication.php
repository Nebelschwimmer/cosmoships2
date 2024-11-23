<?php

namespace App\Entity;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\PublicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class Publication
{

    use TimestampableEntity;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?int $spaceship_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $likes_count = null;

    public function __construct()
    {

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
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
    public function getSpaceshipId(): ?int
    {
        return $this->spaceship_id;
    }

    public function setSpaceshipId(?int $spaceship_id): static
    {
        $this->spaceship_id = $spaceship_id;

        return $this;
    }

    public function getLikesCount(): ?int
    {
        return $this->likes_count;
    }

    public function setLikesCount(?int $likes_count): static
    {
        $this->likes_count = $likes_count;

        return $this;
    }

}
