<?php

namespace App\Controller;

use App\Entity\User;
use App\Dto\UserDto;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
  #[Route('/api/register', name: 'api_register', methods: ['POST'])]
  public function register(
    #[MapRequestPayload(
    acceptFormat: 'json',
  )] UserDto $userDto,
    UserPasswordHasherInterface $passwordHasher,
    EntityManagerInterface $entityManager,
  ): Response {
    $user = User::createFromDto($userDto);

    $user
    ->setPassword($passwordHasher
    ->hashPassword($user, $userDto->password))
    ->setRoles(['ROLE_USER']);
    $entityManager->persist($user);
    $entityManager->flush();

    return $this->json($user, Response::HTTP_CREATED);
  }



  #[Route('/api/user/list', name: 'api_user_list', methods: ['GET'])]
  public function index(UserRepository $userRepository): Response
  {
    return $this->json($userRepository->findAll());
  }


  #[Route('/api/user/{id}', name: 'api_user_show', methods: ['GET'])]
  public function show(User $user): Response
  {
    return $this->json($user);
  }

  #[Route('/api/user/{id}/delete', name: 'api_user_soft_delete', methods: ['DELETE'])]
  public function softDelete(
    User $user,
    EntityManagerInterface $entityManager,
    #[Autowire('%avatars_directory%')] string $avatarsDir,
  ): Response {
    $imageFileName = explode('/', $user->getAvatar())[count(explode('/', $user->getAvatar())) - 1];
    $fileRealPath = $avatarsDir . '/' . $imageFileName;
    if (file_exists($fileRealPath)) {
      unlink($fileRealPath);
    }
    $user
      ->setAvatar('/uploads/user_avatars/ghost.jpg')
      ->setStatus('deleted')
    ;


    $entityManager->persist($user);
    $entityManager->flush();
    return $this->json(['status' => 'deleted']);
  }

  #[Route('/api/user/{id}/edit', name: 'api_user_edit', methods: ['POST', 'PUT'])]
  public function edit(
    #[MapRequestPayload()]
    UserDto $userDto,
    EntityManagerInterface $entityManager,
    User $user
  ): Response {
    $newUser = User::updateFromDto($user, $userDto);
    $entityManager->persist($user);
    $entityManager->flush();
    return $this->json($newUser);
  }



  #[Route('/api/user/{id}/upload', name: 'api_user_upload', methods: ['POST'])]
  public function upload(
    Request $request,
    FileUploader $fileUploader,
    User $user,
    EntityManagerInterface $entityManager,
    #[Autowire('%avatars_directory%')] string $avatarsDir,
  ): Response {

    /** @var UploadedFile $uploadedAvatar */
    $uploadedAvatar = $request->files->get('imageFile');

    if ($uploadedAvatar) {
      $imageFileName = $fileUploader->upload($uploadedAvatar, $avatarsDir);
      if (null !== $imageFileName) {
        $fullpath = $avatarsDir . '/' . $imageFileName;
        $fileUrl = '/uploads/user_avatars' . '/' . $imageFileName;
        if (file_exists($fullpath)) {
          $user->setAvatar($fileUrl);
          $entityManager->persist($user);
          $entityManager->flush();
        } else {
          return $this->json(['message' => 'File not found.'], Response::HTTP_BAD_REQUEST);
        }
      } else {
        return $this->json(['message' => 'File not uploaded because of an error.'], Response::HTTP_BAD_REQUEST);
      }
    }
    return $this->json($user);
  }

}