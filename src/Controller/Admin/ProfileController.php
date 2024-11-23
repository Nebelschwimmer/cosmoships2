<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class ProfileController extends AbstractController
{
  #[Route('/profile/admin', name: 'profile_admin', methods: ['GET'])]

  public function index(): Response
  {
    $user = $this->getUser();

    return $this->render('admin/profile.html.twig', [
      'user' => $user
    ]);

  }
}