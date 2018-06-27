<?php

namespace Xima\DepmonBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xima\DepmonBundle\Service\Aggregator;
use Xima\DepmonBundle\Service\Cache;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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

    public function __construct(Aggregator $aggregator, Cache $cache)
    {
        $this->aggregator = $aggregator;
        $this->cache = $cache;

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
            ->setHelp('This command is fetching all composer informations of every project.')
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

                $data = $this->aggregator->fetchProjectData($project);
                $count = count($data['dependencies']);
                $dependencyCount += $count;

                $this->cache->set($project['name'], $data);

                $projectState = '';
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
                }

                $progressBar->advance();
                $output->writeln(" <fg=blue;options=bold>[" . $project['name'] . "]</> ($count dependencies) – " . $projectState);

            } catch (InvalidArgumentException $e) {

            }
        }


        $metadata = [
            'time' => time(),
            'dependencyCount' => $dependencyCount
        ];

        $this->cache->set('metadata', $metadata);
    }
}