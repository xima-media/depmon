<?php

namespace Xima\DepmonBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class Aggregator
 * @package Xima\DepmonBundle\Service
 */
class Aggregator
{
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $projectTypes = [
        'typo3/cms' => 'TYPO3',
        'symfony/symfony' => 'Symfony',
        'drupal/drupal' => 'Drupal'
    ];

    public function __construct(LoggerInterface $logger, AdapterInterface $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /*
     * Get dependency data for a specific project by cloning the project composer files in the cache dir, installing
     * all dependencies (necessary for using composer show) and fetching the dependency information by "composer show"
     *
     * @param array $project
     * @return array
     */
    public function fetchProjectData($project): array {

        $projectName = $project['name'];
        $this->logger->info("Fetching project dependencies for: $projectName");

        // If project already exists, just pull updates. Otherwise clone the repository.
        if (is_dir('var/data/' . $project['name'])) {
            $process = new Process('cd var/data/' . $project['name'] . ' && git reset --hard origin/master');
        } else {
            $process = new Process('git clone -n ' . $project['git'] . ' var/data/' . $project['name'] . ' --depth 1 -b master --single-branch');
        }

        $process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Preparing composer project setup
        // ToDo: Is it possible to combine multiple processes in a better way?
        $process = new Process(
            // Change to caching directory
            'cd var/data/' . $project['name'] . '/ && ' .
            // Checkout composer.json file
            'git checkout HEAD ' . $project['path'] . 'composer.json && ' .
            // Checkout composer.lock file
            'git checkout HEAD ' . $project['path'] . 'composer.lock && ' .
            // CHange directory
            'cd ' . $project['path'] . ' && ' .
            // Install composer dependencies
            'composer install --no-dev --no-autoloader --no-scripts --ignore-platform-reqs'
        );
        $process->setTimeout(3600);
        $process->run();

        $process = new Process(
            'cd var/data/' . $project['name'] . ' && ' .
            'git describe --tags $(git rev-list --tags --max-count=1)'
        );
        $process->run();
        $gitTag = $process->getOutput();

        $process = new Process(
            'cd var/data/' . $project['name'] . '/' . $project['path'] . ' && ' .
            'composer show --latest --minor-only --format json'
        );
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = [];

        // Saving composer information about project to result
        $result['composer'] = json_decode(file_get_contents('var/data/' . $project['name'] . '/' . $project['path'] . 'composer.json'));
        $result['self'] = $project;

        $requiredPackagesCount = 0;
        $statesCount = [
            1 => 0,
            2 => 0,
            3 => 0
        ];
        $projectState = 1;

        foreach (json_decode($process->getOutput())->installed as $dependency) {
            // Workaround for adding requirement information to dependencies
            foreach ($result['composer']->require as $name => $require) {
                if ($dependency->name == $name) {
                    $dependency->required = $require;
                    $requiredPackagesCount++;
                }

                // Which project type?
                if (array_key_exists($name, $this->projectTypes)) {
                    $result['self']['projectType'] = $this->projectTypes[$name];
                }
            }

            // Is dependency outdated?
            if (isset($dependency->latest)) {
                $state = $this->compareVersions($dependency->version, $dependency->latest);
                $statesCount[$state]++;
                $dependency->state = $state;
            } else {
                $dependency->state = 1;
            }


            $result['dependencies'][] = $dependency;
        }

        if ($statesCount[3] > 1) {
            $projectState = 3;
        } elseif ($statesCount[3] <= 1 && $statesCount[2] >= 1) {
            $projectState = 2;
        }

        $metadata = [
            'requiredPackagesCount' => $requiredPackagesCount,
            'statesCount' => $statesCount,
            'projectState' => $projectState,
            'gitTag' => $gitTag
        ];

        $result['meta'] = $metadata;

        return $result;
    }

    /**
     * Compare versions to check if they are:
     * 1 - Up to date
     * 2 - Pinned, out of date
     * 3 - Out of date
     *
     * @param $version1
     * @param $version2
     * @return int
     */
    private function compareVersions($version1, $version2) {
        $version1 = explode('.', $version1);
        $version1[0] = $version1[0][0] == 'v' ? substr($version1[0], 1) : $version1[0];
        $version2 = explode('.', $version2);
        $version2[0] = $version2[0][0] == 'v' ? substr($version2[0], 1) : $version2[0];

        if ($version1[0] != $version2[0] || (isset($version1[1]) && isset($version2[1]) && $version1[1] != $version2[1])) {
            return 3;
        } else if (isset($version1[2]) && isset($version2[2]) && $version1[2] != $version2[2]) {
            return 2;
        }
        return 1;
    }
}