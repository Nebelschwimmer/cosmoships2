<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Phinx\Console\PhinxApplication;

class PhinxService
{
  private string $projectDirectory;
  public function __construct(
    #[Autowire('%kernel.project_dir%')] string $projectDir
  ) {
    $this->projectDirectory = $projectDir;
  }
  #[Route('/admin/db/seed', name: 'db_seed')]
  public function seed(): array
  {

    $phinx = new PhinxApplication();
    $command = $phinx->find('seed:run');
    try {
      $stream = fopen('php://temp', 'w+');
      $arguments = [
        'command' => 'seed:run',
        '--configuration' => $this->projectDirectory . DIRECTORY_SEPARATOR . 'phinx.php',
      ];

      $output = new \Symfony\Component\Console\Output\StreamOutput($stream);
      $output->setDecorated(false);
      $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL)
      ;

      $status = $command->run(new ArrayInput($arguments), $output);
      $text = stream_get_contents($stream, -1, 0);
    } catch (\Exception $e) {
      $text = $e->getMessage();
      $status = 1;
    }
    fclose($stream);

    $log = [
      'text' => $text,
      'status' => $status,
    ];
    return $log;
  }


}