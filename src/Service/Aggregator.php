<?php

namespace Xima\DepmonBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Xima\DepmonBundle\Util\VersionHelper;

/**
 * Class Aggregator
 * @package Xima\DepmonBundle\Service
 */
class Aggregator
{

    /**
     * @var array
     * ToDo: Move them to the config
     * ToDo: Not really good coverage at all ...
     */
    private $projectTypes = [
        'typo3/cms' => 'TYPO3',
        'symfony/symfony' => 'Symfony',
        'drupal/drupal' => 'Drupal'
    ];


    /**
     * Get dependency data for a specific project by cloning the project composer files in the cache dir, installing
     * all dependencies (necessary for using composer show) and fetching the dependency information by "composer show"
     * ToDo: Check if git/composer is installed
     * ToDo: Optionally remove vendors after composer show was running
     *
     * @param array $project
     * @throws \Exception
     * @return array
     */
    public function fetchProjectData($project, LoggerInterface $logger): array
    {
        $projectName = $project['name'];

        // Check if git is installed
        if ($this->runProcess('command -v \'git\' || which \'git\' || type -p \'git\'') == '') {
            throw new \Exception('Git is not installed!');
        }

        // Check if composer is installed
        if ($this->runProcess('command -v \'composer\' || which \'composer\' || type -p \'composer\'') == '') {
            throw new \Exception('Composer is not installed!');
        }

        // If project already exists, just pull updates. Otherwise clone the repository.
        // ToDo: "git reset" pulls every file of the git
        if (is_dir('var/data/' . $project['name'])) {
            $this->runProcess('cd var/data/' . $project['name'] . ' && git reset --hard origin/master');
        } else {
            $logger->info('Clone project');
            $this->runProcess('git clone -n ' . $project['git'] . ' var/data/' . $project['name'] . ' --depth 1 -b master --single-branch');
        }

        // Check if composer.json exists in project
        $process = $this->runProcess(
            'cd var/data/' . $project['name'] . '/ && ' .
            'git cat-file -e origin/master:' . $project['path'] . 'composer.json && echo true'
        );

        if (trim($process) != 'true') {
            throw new \Exception('No composer.json found in the project ' . $projectName);
        }

        // Check if composer.lock exists in project
        $process = $this->runProcess(
            'cd var/data/' . $project['name'] . '/ && ' .
            'git cat-file -e origin/master:' . $project['path'] . 'composer.lock && echo true'
        );
        $composerLock = false;

        if (trim($process) == 'true') {
            $composerLock = true;
        }

        // Preparing composer project setup
        // ToDo: Is it possible to combine multiple processes in a better way?
        $this->runProcess(
            'cd var/data/' . $project['name'] . '/ && ' .
            // Checkout composer.json file
            'git checkout HEAD ' . $project['path'] . 'composer.json && ' .
            // Checkout composer.lock file
            (($composerLock) ? 'git checkout HEAD ' . $project['path'] . 'composer.lock && ' : '') .
            // Change directory
            (($project['path'] != '') ? 'cd ' . $project['path'] . ' && ' : '') .
            // Install composer dependencies
            'composer install --no-dev --no-autoloader --no-scripts --ignore-platform-reqs --prefer-dist'
        );

        $gitTag = $this->runProcess(
            'cd var/data/' . $project['name'] . ' && ' .
            'git describe --tags $(git rev-list --tags --max-count=1)'
        );

        $data = json_decode($this->runProcess(
            'cd var/data/' . $project['name'] . '/' . $project['path'] . ' && ' .
            'composer show --latest --minor-only --format json'
        ));

        if (empty($data)) {
            throw new \Exception('Empty result of "composer show" for project ' . $projectName);
        }

        $vulnerabilities = json_decode($this->runProcess(
            'curl -H "Accept: application/json" https://security.sensiolabs.org/check_lock -F lock=@var/data/' . $project['name'] . '/' . $project['path'] . 'composer.lock'
        ));

        //
        // Evaluate data
        //

        $result = [];

        // Saving composer information about project to result
        $result['composer'] = json_decode(file_get_contents('var/data/' . $project['name'] . '/' . $project['path'] . 'composer.json'));
        $result['self'] = $project;

        $requiredPackagesCount = 0;
        $statesCount = [
            VersionHelper::STATE_UP_TO_DATE => 0,
            VersionHelper::STATE_PINNED_OUT_OF_DATE => 0,
            VersionHelper::STATE_OUT_OF_DATE => 0,
            VersionHelper::STATE_INSECURE => 0
        ];
        $requiredStatesCount = [
            VersionHelper::STATE_UP_TO_DATE => 0,
            VersionHelper::STATE_PINNED_OUT_OF_DATE => 0,
            VersionHelper::STATE_OUT_OF_DATE => 0,
            VersionHelper::STATE_INSECURE => 0
        ];
        $projectState = VersionHelper::STATE_UP_TO_DATE;

        foreach ($data->installed as $dependency) {
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
                $require = isset($dependency->required) ? $dependency->required : null;
                $state = VersionHelper::compareVersions($dependency->version, $dependency->latest, $require);
                $statesCount[$state]++;
                if ($require) {
                    $requiredStatesCount[$state]++;
                }
                $dependency->state = $state;
            } else {
                $dependency->state = VersionHelper::STATE_UP_TO_DATE;
            }

            // Is dependency a security issue?
            foreach ($vulnerabilities as $name => $vulnerability) {
                if ($name == $dependency->name) {
                    $dependency->state = VersionHelper::STATE_INSECURE;
                    $array = [];
                    foreach ($vulnerability->advisories as $advisory) {
                        array_push($array, $advisory);
                    }
                    $dependency->vulnerability = $array;
                    if (isset($dependency->required)) {
                        $requiredStatesCount[VersionHelper::STATE_INSECURE]++;
                    } else {
                        $statesCount[VersionHelper::STATE_INSECURE]++;
                    }
                }
            }

            $result['dependencies'][] = $dependency;
        }

        if ($requiredStatesCount[4] > 0) {
            $projectState = VersionHelper::STATE_INSECURE;
        } elseif ($requiredStatesCount[3] > 2) {
            $projectState = VersionHelper::STATE_OUT_OF_DATE;
        } elseif ($requiredStatesCount[3] <= 2 && $requiredStatesCount[2] >= 1) {
            $projectState = VersionHelper::STATE_PINNED_OUT_OF_DATE;
        }

        $metadata = [
            'requiredPackagesCount' => $requiredPackagesCount,
            'statesCount' => $statesCount,
            'requiredStatesCount' => $requiredStatesCount,
            'projectState' => $projectState,
            'gitTag' => $gitTag
        ];

        $result['meta'] = $metadata;

        return $result;
    }

    /**
     * @param $project
     */
    public function clearProjectData($project)
    {
        $process = new Process(
            'rm -rf var/data/' . $project['name']
        );
        $process->run();
    }

    /**
     * @param $p
     * @return string
     */
    private function runProcess($p) {
        $process = Process::fromShellCommandline($p);

        $process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }
}