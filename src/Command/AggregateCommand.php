<?php

namespace Xima\DepmonBundle\Command;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xima\DepmonBundle\Service\Aggregator;
use Xima\DepmonBundle\Service\Cache;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AggregateCommand
 * @package Xima\DepmonBundle\Command
 */
class AggregateCommand extends ContainerAwareCommand
{

    /**
     * @var Aggregator
     */
    private $aggregator;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Aggregator $aggregator, Cache $cache, LoggerInterface $logger)
    {
        $this->aggregator = $aggregator;
        $this->cache = $cache;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('depmon:aggregate')

            // the short description shown while running "php bin/console list"
            ->setDescription('Aggregate command.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command is fetching all composer information of every project.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);

        $io->text("<fg=red>██████╗ ███████╗██████╗ ███╗   ███╗ ██████╗ ███╗   ██╗</>");
        $io->text("<fg=red>██╔══██╗██╔════╝██╔══██╗████╗ ████║██╔═══██╗████╗  ██║</>");
        $io->text("<fg=red>██║  ██║█████╗  ██████╔╝██╔████╔██║██║   ██║██╔██╗ ██║</>");
        $io->text("<fg=red>██║  ██║██╔══╝  ██╔═══╝ ██║╚██╔╝██║██║   ██║██║╚██╗██║</>");
        $io->text("<fg=red>██████╔╝███████╗██║     ██║ ╚═╝ ██║╚██████╔╝██║ ╚████║</>");
        $io->text("<fg=red>╚═════╝ ╚══════╝╚═╝     ╚═╝     ╚═╝ ╚═════╝ ╚═╝  ╚═══╝</>");

        $io->title("Aggregate project data");

//        $projects = Yaml::parseFile('config/depmon.projects.yaml');
        $projects = $this->getContainer()->getParameter('xima_depmon.projects');

        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->setFormat('verbose');

        $projectCount = count($projects);
        $io->text("Fetching data for $projectCount projects ...");


        $dependencyCount = 0;

        foreach ($projects as $project) {
            try {

                $data = $this->aggregator->fetchProjectData($project, $this->logger);
                $count = count($data['dependencies']);
                $requiredCount = $data['meta']['requiredPackagesCount'];
                $requiredStatesCount = $data['meta']['requiredStatesCount'];
                $dependencyCount += $count;

                $this->cache->set($project['name'], $data);

                switch ($data['meta']['projectState']) {
                    case 1:
                        $projectState = "<fg=green>up to date</>";
                        break;
                    case 2:
                        $projectState = "<fg=yellow>up to date</>";
                        break;
                    case 3:
                    default:
                        $projectState = "<fg=red>out of date</>";
                        break;
                    case 4:
                        $projectState = "<fg=black>insecure</>";
                        break;
                }

                $progressBar->advance();
                $output->writeln(" <fg=blue;options=bold>[" . $project['name'] . "]</> $count dependencies, $requiredCount required (<fg=green>$requiredStatesCount[1]</>, <fg=yellow>$requiredStatesCount[2]</>, <fg=red>$requiredStatesCount[3]</>, <fg=black>$requiredStatesCount[4]</>) ==> " . $projectState);

            } catch (InvalidArgumentException $e) {

            }

            $this->aggregator->clearProjectData($project);
        }


        $metadata = [
            'time' => time(),
            'dependencyCount' => $dependencyCount
        ];

        $this->cache->set('metadata', $metadata);
    }
}