<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VueController extends AbstractController
{

  #[Route(
    path: '/',
    name: 'front',
    methods: ['GET'],
  )]
  #[Route(
    path: '/{route}',
    name: 'vue_routes',
    methods: ['GET'],
    requirements: ['route' => '^(?!.*_wdt|_profiler|security|admin|apiusers|system).+'],
  )]
  public function index(): Response
  {
    return $this->render('front/index.html.twig');
  }
}