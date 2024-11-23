<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
class SpaceshipDto
{

  public function __construct(
    #[Assert\NotBlank(message: 'Name is required')]
    public readonly ?string $name = '',
    public readonly ?string $description = '',
    public readonly ?string $image = '',
    public readonly ?int $categoryId = 1,
    public readonly ?int $userId = null,
  ) {
  }
}