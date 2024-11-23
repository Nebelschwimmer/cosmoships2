<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Enum\SortOptions;
use App\Enum\OrderOptions;
use App\Enum\Gender;
use Symfony\Contracts\Translation\TranslatorInterface;



class AuxiliaryDataController extends AbstractController
{
  public function __construct(
    private TranslatorInterface $translator,
  ) {
  }

  #[Route('/api/sort/list/{locale}', name: 'api_spaceship_sort_list', methods: ['GET'])]
  public function listSortOptions(string $locale): Response
  {

    return $this->json(SortOptions::list($this->translator, $locale), Response::HTTP_OK);
  }

  #[Route('/api/order/list/{locale}', name: 'api_spaceship_order_list', methods: ['GET'])]
  public function listOrderOptions(string $locale): Response
  {

    return $this->json(OrderOptions::list($this->translator, $locale), Response::HTTP_OK);
  }

  #[Route('/api/genders/list/{locale}', name: 'api_spaceship_genders_list', methods: ['GET'])]
  public function listGenders(string $locale): Response
  {
    return $this->json(Gender::list($this->translator, $locale), Response::HTTP_OK);
  }


}