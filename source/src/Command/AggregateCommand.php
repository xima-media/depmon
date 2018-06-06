<?php

namespace App\Command;


use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;

class AggregateCommand extends Command
{

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

        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Test Command',
            '============',
            '',
        ]);

        $arr = [
            [
                'name' => 'latex-bundle',
                'git' => 'https://github.com/xima-media/latex-bundle.git',
                'path' => ''
            ],
            [
                'name' => 'ical-bundle',
                'git' => 'https://github.com/xima-media/ical-bundle.git',
                'path' => ''
            ],
            [
                'name' => 'skd',
                'git' => 'git@github.com:xima-media/webs-skd-relaunch2016-typo3-website.git',
                'path' => 'htdocs/typo3/'
            ],
            [
                'name' => 'evlks',
                'git' => 'git@git.xima.de:opensource/webs-evlks-relaunch2017-typo3-website.git',
                'path' => 'htdocs/typo3/'
            ]
        ];

        $projects = Yaml::parseFile('config/depmon.yaml');

        if ($input->hasArgument('show') && $input->getArgument('show') != null) {
            $cache = new FilesystemCache();
            $output->writeln($input->getArgument('show'));
            if ($cache->has($input->getArgument('show'))) {
                foreach ($cache->get($input->getArgument('show'))['dependencies'] as $item) {
                    if (!isset($item->latest) || $item->version == $item->latest) {
                        $version = '<fg=green>' . $item->version . '</>';
                    } else {
                        $version = '<fg=red>' . $item->version . '</> (' . $item->latest . ')';
                    }
                    $output->writeln('<fg=blue;options=bold>' . $item->name . '</>: ' . $version);
                }
            }
        } else {
            foreach ($projects['projects'] as $project) {
                $this->aggregateProject($output, $project);
            }
        }



    }

    private function aggregateProject($output, $project) {

        $cache = new FilesystemCache(
            'depmon',
            3600,
            'var/cache/test'
        );

        $output->writeln('Project <options=bold>' . $project['name'] . '</>');
        $output->writeln('Git checkout <options=underscore>' . $project['git'] . '</>');

        if (is_dir('var/data/' . $project['name'])) {
            $process = new Process('cd var/data/' . $project['name'] . ' && git pull --force');
            $process->setTimeout(3600);
            $process->run();
        } else {
            $process = new Process('git clone -n ' . $project['git'] . ' var/data/' . $project['name'] . ' --depth 1');
            $process->setTimeout(3600);
            $process->run();
        }


        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $process = new Process('cd var/data/' . $project['name'] . '/ && git checkout HEAD ' . $project['path'] . 'composer.json');
        $process->run();

        $process = new Process('cd var/data/' . $project['name'] . '/ && git checkout HEAD ' . $project['path'] . 'composer.lock');
        $process->run();

        $output->writeln('Composer install');
        $process = new Process('cd var/data/' . $project['name'] . '/' . $project['path'] . ' && composer install --no-dev --no-autoloader --no-scripts --ignore-platform-reqs');
        $process->setTimeout(3600);
        $process->run();

        $process = new Process('cd var/data/' . $project['name'] . '/' . $project['path'] . ' && composer show --latest --minor-only --format json');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = [];

        $result['self'] = json_decode(file_get_contents('var/data/' . $project['name'] . '/' . $project['path'] . 'composer.json'));

        foreach (json_decode($process->getOutput())->installed as $dependency) {
            foreach ($result['self']->require as $name => $require) {
                if ($dependency->name == $name) {
                    $dependency->required = $require;
                }
            }
            $result['dependencies'][] = $dependency;
        }

        $cache->set($project['name'], $result);

        if ($cache->has($project['name'])) {
            foreach ($cache->get($project['name'])['dependencies'] as $item) {
                if (isset($item->required)) {
                    $version = $item->version . '(' . $item->required . ')';
                } else {
                    $version = $item->version;
                }
                $output->writeln('<fg=blue;options=bold>' . $item->name . '</>: ' . $version);
            }
        }
    }
}