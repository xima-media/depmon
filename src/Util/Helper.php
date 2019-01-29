<?php
/**
 * Created by PhpStorm.
 * User: kmi
 * Date: 2019-01-24
 * Time: 14:05
 */

namespace Xima\DepmonBundle\Util;


use Xima\DepmonBundle\Entity\Dependency;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Service\Aggregator;

class Helper
{
    /**
     * @param \DateTime $date
     * @return bool
     */
    public static function isOutdated($date) {
        $isOutdated = false;
        if(strtotime($date) < strtotime('-60 days')) {
            $isOutdated = true;
        }
        return $isOutdated;
    }

    /**
     * @param Project $project
     * @param $data
     * @return Project
     */
    public static function buildUpMetadata($project, $data) {
        $result = [];

        $requiredPackagesCount = 0;
        $statesCount = [
            VersionHelper::STATE_UP_TO_DATE => 0,
            VersionHelper::STATE_PINNED_OUT_OF_DATE => 0,
            VersionHelper::STATE_OUT_OF_DATE => 0,
            VersionHelper::STATE_INSECURE => 0
        ];
        $requiredStatesCount = $statesCount;

        $project->setState(VersionHelper::STATE_UP_TO_DATE);

        // Iterate through installed dependencies
        foreach ($data->installed as $dependencyData) {


            $project->addDependency($dependency);
        }

        if ($requiredStatesCount[4] > 0) {
            $project->setState(VersionHelper::STATE_INSECURE);
        } elseif ($requiredStatesCount[3] > 2) {
            $project->setState(VersionHelper::STATE_OUT_OF_DATE);
        } elseif ($requiredStatesCount[3] <= 2 && $requiredStatesCount[2] >= 1) {
            $project->setState(VersionHelper::STATE_PINNED_OUT_OF_DATE);
        }

        $metadata = [
            'requiredPackagesCount' => $requiredPackagesCount,
            'statesCount' => $statesCount,
            'requiredStatesCount' => $requiredStatesCount,
        ];

        $result['meta'] = $metadata;

        return $project;
    }

    /**
     * @param $dependencyData
     * @param Project $project
     * @return Dependency
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function processDependency($dependencyData, $project) {
        $dependency = new Dependency();
        $dependency->setName($dependencyData->name);
        $dependency->setStable($dependencyData->version);

        // Workaround for adding requirement information to dependencies
        foreach (json_decode(json_decode($project->getComposer()))->require as $name => $require) {
            if ($dependency->getName() == $name) {
                $dependency->setRequired($require);
            }
        }

        // Is dependency outdated?
        if (isset($dependencyData->latest)) {
            $dependency->setLatest($dependencyData->latest);

            $dependency->setState(VersionHelper::compareVersions($dependency->getStable(), $dependency->getLatest(), $dependency->getRequired()));
        } else {
            $dependency->setState(VersionHelper::STATE_UP_TO_DATE);
        }

        // Get additional information
        $additionalData = Aggregator::getDependencyDataFromPackagist($dependency->getName());
        if ($additionalData) {
            $dependency->setLink($additionalData->url);
            $dependency->setDescription($additionalData->description);
        }
        return $dependency;
    }

    public static function expandVulnerabilityInformation() {
        // Is dependency a security issue?
        if (!empty($vulnerabilities)) {
            foreach ($vulnerabilities as $name => $vulnerability) {
                if ($name == $dependency->name) {
                    $dependency->state = VersionHelper::STATE_INSECURE;
                    $array = [];
                    foreach ($vulnerability['advisories'] as $advisory) {
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
    }
}