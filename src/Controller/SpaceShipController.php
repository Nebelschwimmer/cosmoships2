<?php

namespace App\Controller;

use App\Dto\SpaceshipDto;
use App\Entity\Like;
use App\Entity\Publication;
use App\Entity\SpaceShipCategory;
use App\Entity\SpaceShip;
use App\Repository\PublicationRepository;
use App\Repository\SpaceShipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SpaceShipCategoryRepository;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use OpenApi\Attributes as OA;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Schema;






class SpaceShipController extends AbstractController
{

  public function __construct(
    private TranslatorInterface $translator,
  ) {
  }
  #[Route('/api/spaceships', name: 'api_space_ship_index', methods: ['GET'])]
  public function list(
    SpaceShipRepository $spaceShipRepository,
    #[MapQueryParameter('offset')] ?int $offset = 0,
    #[MapQueryParameter('limit')] ?int $limit = 8,
    #[MapQueryParameter('search')] ?string $searchQuery = null,
    #[MapQueryParameter('sortBy')] ?string $sortBy = 'name',
    #[MapQueryParameter('order')] ?string $order = 'ASC',
  ): Response {
    $spaceShips = $spaceShipRepository->findAllWithQueryParams($offset, $limit, $searchQuery, $sortBy, $order);
    $allSpaceships = $spaceShipRepository->findAll();

    $totalSpaceshipsListNumber = count($allSpaceships);

    $maxPages = ceil($totalSpaceshipsListNumber / $limit);

    return $this->json(
      data: [
        'spaceships' => $spaceShips,
        'maxpages' => $maxPages,
        'total' => $totalSpaceshipsListNumber
      ],
      status: Response::HTTP_OK,
      headers: ['content-type' => 'application/json'],
    );
  }


  #[Route('/api/spaceships/{id}', name: 'api_space_ship_item', methods: ['GET'])]
  public function item(SpaceShip $spaceShip): Response
  {

    return $this->json($spaceShip, Response::HTTP_OK);
  }

  #[Route('/api/spaceships/{id}', name: 'api_spaceship_delete_one', methods: ['DELETE'])]
  public function deleteSpaceshipById(
    Request $request,
    SpaceShip $spaceShip,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    #[Autowire('%ships_directory%')] string $shipsDir,
  ): Response {
    $userId = $request->getPayload()->get('userId');
    $user = $userRepository->findUserById($userId);
    if (!$user) {
      return $this->json(['status' => 'user not found'], Response::HTTP_NOT_FOUND);
    }
    $publication = $user->findPublicationBySpaceShipId($spaceShip->getId());
    if ($publication) {
      $user->removePublication($publication);
    } else {
      return $this->json(['status' => 'publication not found'], Response::HTTP_NOT_FOUND);
    }

    $likes = $spaceShip->getLikes();
    if (count($likes) !== 0) {
      foreach ($likes as $like) {

        $spaceShip->removeLike($like);
        $user->removeLike($like);

        $entityManager->persist($spaceShip);
        $entityManager->persist($user);
      }
    }
    $image = $spaceShip->getImage();
    if ($image) {
      $imageFileName = explode('/', $image)[count(explode('/', $spaceShip->getImage())) - 1];
      $fileRealPath = $shipsDir . '/' . $imageFileName;
      if (file_exists($fileRealPath)) {
        unlink($fileRealPath);
      }
    }

    $entityManager->remove($spaceShip);
    $entityManager->flush();

    return $this->json(['status' => 'deleted'], Response::HTTP_OK);
  }

  #[Route('/api/spaceships/categories/list', name: 'api_spaceship_categories_list', methods: ['GET'])]
  public function listCategories(SpaceShipCategoryRepository $spaceShipCategoryRepository): Response
  {

    return $this->json($spaceShipCategoryRepository->findAll(), Response::HTTP_OK);
  }

  #[Route('/api/spaceships/add', name: 'api_spaceship_add', methods: ['POST'])]
  public function add(
    #[MapRequestPayload()] SpaceshipDto $spaceshipDto,
    ValidatorInterface $validator,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    SpaceShipCategoryRepository $spaceShipCategoryRepository,
  ): Response {
    $user = $userRepository->findUserById($spaceshipDto->userId);
    if (!$user) {
      return $this->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
    }

    $errors = $validator->validate($spaceshipDto);

    if (count($errors) > 0) {
      $errorsString = (string) $errors;

      return $this->json(['errors' => $errorsString], Response::HTTP_BAD_REQUEST);
    } else {
      $spaceShip = SpaceShip::createFromDto($user, $spaceshipDto);
    }

    $category = new SpaceShipCategory($this->translator);
    $category = $spaceShipCategoryRepository->find($spaceshipDto->categoryId);

    $spaceShip
      ->setCategory($category);

    $entityManager->persist($spaceShip);
    $entityManager->flush();

    $publication = new Publication();
    $publication
      ->setName($spaceshipDto->name)
      ->setSpaceshipId($spaceShip->getId())
      ->setType('space_ship')
      ->setLikesCount(0)
    ;
    $user->addPublication($publication);
    $entityManager->persist($user);
    $entityManager->persist($publication);
    $entityManager->flush();

    return $this->json($spaceShip, Response::HTTP_CREATED);
  }


  #[Route('/api/spaceships/{id}/upload', name: 'api_spaceship_upload', methods: ['POST'])]
  public function upload(
    Request $request,
    FileUploader $fileUploader,
    SpaceShip $spaceShip,
    EntityManagerInterface $entityManager,
    #[Autowire('%ships_directory%')] string $shipsDir,
  ): Response {

    /** @var UploadedFile $uploadedImageFile */
    $uploadedImageFile = $request->files->get('imageFile');

    if ($uploadedImageFile) {
      $imageFileName = $fileUploader->upload($uploadedImageFile, $shipsDir);
      if (null !== $imageFileName) {
        $fullpath = $shipsDir . '/' . $imageFileName;
        $fileUrl = '/uploads/spaceships' . '/' . $imageFileName;
        if (file_exists($fullpath)) {
          $spaceShip->setImage($fileUrl);
          $entityManager->persist($spaceShip);
          $entityManager->flush();
        } else {
          return $this->json(['message' => 'File not found.'], Response::HTTP_BAD_REQUEST);
        }
      } else {
        return $this->json(['message' => 'File not uploaded because of an error.'], Response::HTTP_BAD_REQUEST);
      }
    }

    return $this->json($spaceShip);
  }


  #[Route('/api/spaceships/{id}/edit', name: 'api_spaceship_edit', methods: ['POST', 'PUT'])]
  public function edit(
    #[MapRequestPayload(
    acceptFormat: 'json',
  )] SpaceshipDto $spaceshipDto,
    SpaceShip $spaceShip,
    EntityManagerInterface $entityManager,
    SpaceShipCategoryRepository $spaceShipCategoryRepository,

  ): Response {
    $category = new SpaceShipCategory($this->translator);
    $category = $spaceShipCategoryRepository->find($spaceshipDto->categoryId);
    $updatedSpaceship = SpaceShip::updateFromDto($spaceShip, $category, $spaceshipDto);

    $user = $spaceShip->getUser();
    $publication = $user->findPublicationBySpaceShipId($spaceShip->getId());
    if ($publication) {
      $publication->setName($spaceshipDto->name);
      $user->updatePublication($publication);
    }
    $entityManager->persist($updatedSpaceship);
    $entityManager->flush();

    return $this->json($updatedSpaceship, Response::HTTP_OK);
  }

  #[Route('/api/spaceships/{id}/like', name: 'api_spaceship_like_add', methods: ['POST'])]
  public function addLike(
    Request $request,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    SpaceShipRepository $spaceShipRepository,
    PublicationRepository $publicationRepository,
    int $id,
  ): Response {
    $user = $userRepository->findUserById($request->getPayload()->get('userId'));
    $spaceShip = $spaceShipRepository->find($id);

    $like = new Like();
    $like->setUser($user);
    $spaceShip->addLike($like);

    $publication = $publicationRepository->findOneBy(['spaceship_id' => $spaceShip->getId()]);
    if ($publication) {
      $likesCount = $publication->getLikesCount();
      $publication->setLikesCount($likesCount + 1);
      $entityManager->persist($publication);
    }

    $user->addLike($like);

    $entityManager->persist($spaceShip);
    $entityManager->persist($like);
    $entityManager->persist($user);
    $entityManager->flush();

    return $this->json($spaceShip, Response::HTTP_CREATED);
  }

  #[Route('/api/spaceships/{id}/like', name: 'api_spaceship_like_delete', methods: ['DELETE'])]
  public function removeLike(
    Request $request,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    SpaceShip $spaceShip,
    PublicationRepository $publicationRepository
  ): Response {
    $user = $userRepository->findUserById($request->getPayload()->get('userId'));
    $publication = $publicationRepository->findOneBy(['spaceship_id' => $spaceShip->getId()]);

    $publication = $user->findPublicationBySpaceShipId($spaceShip->getId());

    if ($publication) {
      $publication->setLikesCount($publication->getLikesCount() - 1);
    }
    $likes = $spaceShip->getLikes();
    foreach ($likes as $like) {
      if ($like->getUser() === $user) {
        $spaceShip->removeLike($like);
        $user->removeLike($like);
        $entityManager->persist($spaceShip);
        $entityManager->persist($user);
        $entityManager->flush();
      }
    }
    
    return $this->json($spaceShip, Response::HTTP_OK);
  }

}