<?php

namespace App\Entity;
use App\Dto\UserDto;
use App\Entity\Publication;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Ignore;
use Doctrine\DBAL\Types\Types;
use App\Enum\Gender;
use App\Enum\Roles;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const DEFAULT_GENDER = Gender::MALE;
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(nullable: true)]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $username = null;


    #[ORM\Column(length: 180)]
    private ?string $email = null;


    #[ORM\Column(length: 180, nullable: true)]
    private ?string $avatar = null;


    #[ORM\Column(nullable: true)]
    private ?string $lastLogin = null;

    #[ORM\Column(type: Types::SMALLINT, enumType: Gender::class)]
    private ?Gender $gender = self::DEFAULT_GENDER;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $about = null;

    #[ORM\Column(nullable: true)]
    private ?string $dateOfBirth;

    #[ORM\Column]
    private array $roles = [];

    #[Ignore]
    #[ORM\Column]
    private ?string $password = null;


    #[ORM\Column(length: 180)]
    private ?string $status = 'active';

    #[ORM\JoinTable(name: 'publications_to_user')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'publication_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Publication', cascade: ['persist'])]
    private ArrayCollection|PersistentCollection $publications;

    #[ORM\JoinTable(name: 'likes_to_user')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'like_id', referencedColumnName: 'id', unique: true)]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity:
        Like::class, cascade: ['persist', 'remove'])]
    private ArrayCollection|PersistentCollection $likes;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public static function createFromDto(UserDto $dto): User
    {
        $user = new self();
        $user
            ->setUsername($dto->username)
            ->setPassword($dto->password)
            ->setEmail($dto->email)
            ->setAvatar($dto->avatar)
            ->setGender($dto->gender)
            ->setAbout($dto->about)
            ->setDateOfBirth($dto->dateOfBirth)
        ;

        return $user;
    }
    public static function updateFromDto(User $user, UserDto $dto): User
    {
        $user->setUsername($dto->username)
            ->setAvatar($dto->avatar)
            ->setGender($dto->gender)
            ->setAbout($dto->about)
            ->setDateOfBirth($dto->dateOfBirth);

        return $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }


    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function getPublications(): ArrayCollection|PersistentCollection
    {
        return $this->publications;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function setPublications(ArrayCollection|PersistentCollection $publications): static
    {
        $this->publications = $publications;

        return $this;
    }

    public function addPublication(Publication $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
        }

        return $this;
    }
    public function findPublicationBySpaceShipId(int $spaceship_id): ?Publication
    {
        foreach ($this->publications as $publication) {
            if ($publication->getSpaceshipId() === $spaceship_id) {
                return $publication;
            }
        }
        return null;
    }
    public function updatePublication(Publication $publication): static
    {
        if ($this->publications->contains($publication)) {
            $this->publications->removeElement($publication);
            $this->publications->add($publication);
        }

        return $this;
    }
    public function removePublication(Publication $publication): static
    {
        if ($this->publications->contains($publication)) {
            $this->publications->removeElement($publication);
        }

        return $this;
    }
    public function setLikes(ArrayCollection|PersistentCollection $likes): static
    {
        $this->likes = $likes;

        return $this;
    }

    public function addLike(Like $like): void
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
        }
    }

    public function removeLike(Like $like): void
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
        }
    }

    public function getLikes(): ArrayCollection|PersistentCollection
    {
        return $this->likes;
    }


    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function getGenderString(): string
    {
        return $this->gender->name;
    }

    public function setGender(Gender|int $gender): static
    {
        $this->gender = is_int($gender) ? Gender::from($gender) : $gender;

        return $this;
    }
    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): static
    {
        $this->about = $about;
        return $this;
    }

    public function getDateOfBirth(): string
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(string $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }

}
