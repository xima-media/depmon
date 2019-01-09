<?php

namespace Xima\DepmonBundle\Service;

use function Deployer\writeln;
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
     * @throws \Exception
     */
    public function checkIfGitIsInstalled() {
        // Check if git is installed
        if ($this->runProcess('command -v \'git\' || which \'git\' || type -p \'git\'') == '') {
            throw new \Exception('Git is not installed!');
        }
    }

    /**
     * @throws \Exception
     */
    public function checkIfComposerIsInstalled() {
        // Check if composer is installed
        if ($this->runProcess('command -v \'composer\' || which \'composer\' || type -p \'composer\'') == '') {
            throw new \Exception('Composer is not installed!');
        }
    }

    /**
     * @param array $project
     */
    public function updateProjectData($project) {
        // If project already exists, just pull updates. Otherwise clone the repository.
        // ToDo: "git reset" pulls every file of the git
        if (is_dir('var/data/' . $project['name'])) {
            $this->runProcess('cd var/data/' . $project['name'] . ' && git pull --rebase');
        } else {
            $this->runProcess('git clone -n ' . $project['git'] . ' var/data/' . $project['name'] . ' --depth 1 -b master --single-branch');
        }
    }

    /**
     * @param $project
     * @throws \Exception
     */
    public function checkIfComposerJsonExists($project) {
        // Check if composer.json exists in project
        $process = $this->runProcess(
            'cd var/data/' . $project['name'] . '/ && ' .
            'git cat-file -e origin/master:' . $project['path'] . 'composer.json && echo true'
        );

        if (trim($process) != 'true') {
            throw new \Exception('No composer.json found in the project ' . $project['name']);
        }
    }

    /**
     * @param $project
     * @return bool
     */
    public function checkIfComposerLockExists($project) {
        // Check if composer.lock exists in project
        $process = $this->runProcess(
            'cd var/data/' . $project['name'] . '/ && ' .
            'git cat-file -e origin/master:' . $project['path'] . 'composer.lock && echo true'
        );

        if (trim($process) == 'true') {
            return true;
        }

        return false;
    }

    /**
     * @param $project
     * @return bool
     */
    public function validateComposerFiles($project) {
        // Check if composer.lock exists in project
        $process = $this->runProcess(
            'cd var/data/' . $project['name'] . '/' . $project['path'] . ' && ' .
            'composer validate --strict'
        );

        if($process == 'error') {
            return false;
        }
        return true;
    }

    /**
     * @param array $project
     * @param bool $composerLock
     */
    public function installComposerDependencies($project, $composerLock) {
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
    }

    /**
     * @param array $project
     * @return string
     */
    public function fetchGitTag($project) {
        return trim($this->runProcess(
            'cd var/data/' . $project['name'] . ' && ' .
            'git describe --tags $(git rev-list --tags --max-count=1)'
        ));
    }

    /**
     * @param $project
     * @return
     */
    public function getLastModificationDate($project) {
        $timestamp = trim($this->runProcess(
            'cd var/data/' . $project['name'] . ' && ' .
            'git show -s --format=%ct --date=local'
        ));
        $isOutdated = false;
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new \DateTimeZone('Europe/Berlin'));

        if($timestamp < strtotime('-60 days')) {
            $isOutdated = true;
        }
        return [$date->format("d.m.Y, H:m"), $isOutdated];
    }

    /**
     * @param array $project
     * @return mixed
     * @throws \Exception
     */
    public function fetchComposerData($project) {
        $data = json_decode($this->runProcess(
            'cd var/data/' . $project['name'] . '/' . $project['path'] . ' && ' .
            'composer show --latest --minor-only --format json'
        ));

        if (empty($data)) {
            throw new \Exception('Empty result of "composer show" for project ' . $project['name']);
        }

        return $data;
    }

    /**
     * @param array $project
     * @return mixed
     */
    public function checkVulnerabilities($project) {
        return json_decode($this->runProcess(
            'curl -H "Accept: application/json" https://security.sensiolabs.org/check_lock -F lock=@var/data/' . $project['name'] . '/' . $project['path'] . 'composer.lock'
        ), true);
    }

    /**
     * @param $project
     * @param $data
     * @param $vulnerabilities
     * @param $gitTag
     * @param $modificationDate
     * @return array
     */
    public function buildUpMetadata($project, $data, $vulnerabilities, $gitTag, $modificationDate) {
        $result = [];

        // Saving composer information about project to result
        $result['composer'] = json_decode(file_get_contents('var/data/' . $project['name'] . '/' . $project['path'] . 'composer.json'));
        $result['self'] = $project;
        $result['date'] = $modificationDate;

        $requiredPackagesCount = 0;
        $statesCount = [
            VersionHelper::STATE_UP_TO_DATE => 0,
            VersionHelper::STATE_PINNED_OUT_OF_DATE => 0,
            VersionHelper::STATE_OUT_OF_DATE => 0,
            VersionHelper::STATE_INSECURE => 0
        ];
        $requiredStatesCount = $statesCount;
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
            if (!empty($vulnerabilities)) {
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
     * @param array $project
     */
    public function clearProjectData($project)
    {
        $process = $this->runProcess(
            'rm -rf var/data/' . $project['name']
        );
    }

    /**
     * @param $p
     * @return string
     */
    private function runProcess($command, $strict = false) {
        $process = Process::fromShellCommandline($command);

        $process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            if ($strict) {
                throw new ProcessFailedException($process);
            } else {
                return 'error';
            }
        }
        return $process->getOutput();
    }
}