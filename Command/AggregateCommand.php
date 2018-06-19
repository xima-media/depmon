<?php

namespace Xima\DepmonBundle\Command;


use Xima\DepmonBundle\Service\Aggregator;
use Xima\DepmonBundle\Service\Cache;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AggregateCommand
 * @package Xima\DepmonBundle\Command
 */
class AggregateCommand extends Command
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
            ->setName('app:aggregate')

            // the short description shown while running "php bin/console list"
            ->setDescription('Aggregate command.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command is fetching all composer informations of every project.')

            ->addArgument('show')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln([
            'Aggregator Command',
            '============',
            '',
        ]);

        $projects = Yaml::parseFile('config/depmon.projects.yaml');

        $progressBar = new ProgressBar($output, count($projects['projects']));
        $progressBar->setFormat('verbose');

        foreach ($projects['projects'] as $project) {
            try {
                $this->cache->set($project['name'], $this->aggregator->fetchProjectData($project));

                $progressBar->advance();
                $output->writeln(' <fg=red;options=bold>' . $project['name'] . '</>');

            } catch (InvalidArgumentException $e) {

            }
        }

        $this->cache->set('metadata', time());

        $progressBar->finish();
    }
}