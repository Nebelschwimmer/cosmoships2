<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class CommandsController extends AbstractController
{
  #[Route('/api/spaceships/import', name: 'api_spaceship_pull', methods: ['POST'])]
  public function grabSpaceships(KernelInterface $kernel, Request $request): Response
  {
    $numberToImport = $request->query->get('number', 10);
    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'spaceships:import',
      'count' => $numberToImport,
    ]);

    $output = new BufferedOutput();
    $application->run($input, $output);

    return $this->json(['message' => 'SpaceShips imported successfully.']);
  }
  #[Route('/api/migrations/run', name: 'api_migrations_run', methods: ['POST'])]
  public function migrate(KernelInterface $kernel): Response
  {
    $application = new Application($kernel);
    $application->setAutoExit(false);
    $input = new ArrayInput([
      'command' => 'doctrine:migrations:migrate',
      '--allow-no-migration' => true,
      '--no-interaction' => true,
    ]);


    $output = new BufferedOutput();
    $application->run($input, $output);
    return $this->json(['message' => 'SpaceShips migrated successfully.']);
  }

  #[Route('/api/database/seed', name: 'admin_seed_data', methods: ['POST', 'GET'])]
  public function seed(KernelInterface $kernel): Response
  {
    $application = new Application($kernel);
    $application->setAutoExit(false);
    $input = new ArrayInput([
      'command' => 'vendor/robmorgan/phinx/bin/phinx seed:run',
      '--no-interaction' => true,
    ]);

    $output = new BufferedOutput();
    $application->run($input, $output);
    return $this->redirect($this->generateUrl('admin'));
  }
}