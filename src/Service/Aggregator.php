<?php

namespace Xima\DepmonBundle\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Util\VersionHelper;

/**
 * Class Aggregator
 * @package Xima\DepmonBundle\Service
 */
class Aggregator
{
    /**
     * @return bool
     * @throws \Exception
     */
    public static function checkIfGitIsInstalled() {
        // Check if git is installed
        if (Aggregator::runProcess('command -v \'git\' || which \'git\' || type -p \'git\'') == '') {
            throw new \Exception('Git is not installed!');
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function checkIfComposerIsInstalled() {
        // Check if composer is installed
        if (Aggregator::runProcess('command -v \'composer\' || which \'composer\' || type -p \'composer\'') == '') {
            throw new \Exception('Composer is not installed!');
        }
        return true;
    }

    /**
     * @param Project $project
     */
    public static function updateProjectData($project) {
        // If project already exists, just pull updates. Otherwise clone the repository.
        if (is_dir('var/data/' . $project->getName())) {
            Aggregator::runProcess('cd var/data/' . $project->getName() . ' && git pull --rebase');
        } else {
            Aggregator::runProcess('git clone -n ' . $project->getGit() . ' var/data/' . $project->getName() . ' --depth 1 -b master --single-branch');
        }
    }

    /**
     * @param Project $project
     * @return bool
     * @throws \Exception
     */
    public static function checkIfComposerJsonExists($project) {
        // Check if composer.json exists in project
        $process = Aggregator::runProcess(
            'cd var/data/' . $project->getName() . '/ && ' .
            'git cat-file -e origin/master:' . $project->getPath() . 'composer.json && echo true'
        );

        if (trim($process) != 'true') {
            throw new \Exception('No composer.json found in the project ' . $project->getName());
        }
        return true;
    }

    /**
     * @param Project $project
     * @return bool
     */
    public static function checkIfComposerLockExists($project) {
        // Check if composer.lock exists in project
        $process = Aggregator::runProcess(
            'cd var/data/' . $project->getName() . '/ && ' .
            'git cat-file -e origin/master:' . $project->getPath() . 'composer.lock && echo true'
        );

        if (trim($process) == 'true') {
            return true;
        }

        return false;
    }

    /**
     * @param Project $project
     * @return bool
     */
    public static function validateComposerFiles($project) {
        // Check if composer.lock exists in project
        $process = Aggregator::runProcess(
            'cd var/data/' . $project->getName() . '/' . $project->getPath() . ' && ' .
            'composer validate --strict'
        );

        if($process == 'error') {
            return false;
        }
        return true;
    }

    /**
     * @param Project $project
     * @param bool $composerLock
     */
    public static function installComposerDependencies($project, $composerLock) {
        // Preparing composer project setup
        // ToDo: Is it possible to combine multiple processes in a better way?
        Aggregator::runProcess(
            'cd var/data/' . $project->getName() . '/ && ' .
            // Checkout composer.json file
            'git checkout HEAD ' . $project->getPath() . 'composer.json && ' .
            // Checkout composer.lock file
            (($composerLock) ? 'git checkout HEAD ' . $project->getPath() . 'composer.lock && ' : '') .
            // Change directory
            (($project->getPath() != '') ? 'cd ' . $project->getPath() . ' && ' : '') .
            // Install composer dependencies
            'composer install --no-dev --no-autoloader --no-scripts --ignore-platform-reqs --prefer-dist'
        );
    }

    /**
     * @param Project $project
     * @return string
     */
    public static function fetchGitTag($project) {
        return trim(Aggregator::runProcess(
            'cd var/data/' . $project->getName() . ' && ' .
            'git describe --tags $(git rev-list --tags --max-count=1)'
        ));
    }

    /**
     * @param Project $project
     * @return \Datetime
     */
    public static function getLastModificationDate($project) {
        $timestamp = trim(Aggregator::runProcess(
            'cd var/data/' . $project->getName() . ' && ' .
            'git show -s --format=%ct --date=local'
        ));
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new \DateTimeZone('Europe/Berlin'));

        return $date;
    }

    /**
     * @param Project $project
     * @return mixed
     * @throws \Exception
     */
    public static function fetchComposerData($project) {
        $data = json_decode(Aggregator::runProcess(
            'cd var/data/' . $project->getName() . '/' . $project->getPath() . ' && ' .
            'composer show --latest --minor-only --format json'
        ));

        if (empty($data)) {
            throw new \Exception('Empty result of "composer show" for project ' . $project->getName());
        }

        return $data;
    }

    /**
     * @ToDo recently the api has a limited access, seems like only one request per minute is alowed
     *
     * @param Project $project
     * @return mixed
     */
    public static function checkVulnerabilities($project) {
        return json_decode(Aggregator::runProcess(
            'curl -H "Accept: application/json" https://security.symfony.com/check_lock -F lock=@var/data/' . $project->getName() . '/' . $project->getPath() . 'composer.lock'
        ), true);
    }

    /**
     * @ToDo check if result name matches dependency name
     *
     * @param $dependency
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getDependencyDataFromPackagist($dependency) {

        $client = new \GuzzleHttp\Client();
        $request = $client->request('GET', 'https://packagist.org/search.json?q=' . $dependency);

        $data = json_decode($request->getBody());

        if ($data->total != 0) {
            return $data->results[0];
        } else {
            return false;
        }
    }

    /**
     * @param Project $project
     * @return mixed
     */
    public static function getLocalComposerJson($project) {
        return json_encode(file_get_contents('var/data/' . $project->getName() . '/' . $project->getPath() . 'composer.json'));
    }
 
    /**
     * @param Project $project
     */
    public static function clearProjectData($project)
    {
        Aggregator::runProcess(
            'rm -rf var/data/' . $project->getName()
        );
    }

    /**
     * @param $p
     * @return string
     */
    public static function runProcess($command, $strict = false) {
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