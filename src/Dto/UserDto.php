<?php
namespace App\Dto;
use App\Enum\Gender;
class UserDto
{

  public function __construct(
    public readonly ?string $password = '',
    public readonly ?string $email = '',
    public readonly ?string $username = '',
    public readonly ?int $gender = Gender::MALE->value,
    public readonly ?string $avatar = '',
    public readonly ?string $about = '',
    public readonly ?string $dateOfBirth = '',
  ) {
  }
}