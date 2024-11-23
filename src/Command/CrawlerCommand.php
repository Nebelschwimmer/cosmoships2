<?php

namespace App\Command;

use App\Service\SpaceshipsGrabber;
use Doctrine\Migrations\Tools\Console\ConsoleLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'spaceships:import',
    description: 'Import spaceships from API',
)]
class CrawlerCommand extends Command
{
    public function __construct(private readonly SpaceshipsGrabber $spaceshipsGrabber)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('count', InputArgument::OPTIONAL, 'Spaceships number to import')
            ->addOption('dryRun', null, InputOption::VALUE_NONE, 'Import spaceships without flushing them to DB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $input->getArgument('count');
        $dryRun = (bool) $input->getOption('dryRun');
        if ($output->isVerbose()) {
            $logger = new ConsoleLogger($output);
            $this->spaceshipsGrabber->setLogger($logger);
        }
        $this->spaceshipsGrabber->importSpaceships($count, $dryRun);

        return Command::SUCCESS;
    }
}
