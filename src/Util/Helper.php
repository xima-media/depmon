<?php

namespace Xima\DepmonBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
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
     * @return Project
     */
    public static function buildUpMetadata($project) {

        $requiredPackagesCount = 0;
        $totalPackagesCount = 0;
        $statesCount = [
            VersionHelper::STATE_UP_TO_DATE => 0,
            VersionHelper::STATE_PINNED_OUT_OF_DATE => 0,
            VersionHelper::STATE_OUT_OF_DATE => 0,
            VersionHelper::STATE_INSECURE => 0
        ];
        $requiredStatesCount = $statesCount;

        $project->setState(VersionHelper::STATE_UP_TO_DATE);

        foreach ($project->getDependencies() as $dependency) {
            if ($dependency->getRequired() != null) {
                $requiredPackagesCount++;
                $requiredStatesCount[$dependency->getState()]++;
            }
            $totalPackagesCount++;
            $statesCount[$dependency->getState()]++;
        }

        if ($requiredStatesCount[4] > 0) {
            $project->setState(VersionHelper::STATE_INSECURE);
        } elseif ($requiredStatesCount[3] > 2) {
            $project->setState(VersionHelper::STATE_OUT_OF_DATE);
        } elseif ($requiredStatesCount[3] <= 2 && $requiredStatesCount[2] >= 1) {
            $project->setState(VersionHelper::STATE_PINNED_OUT_OF_DATE);
        }

        $metadata = [
            'totalPackagesCount' => $totalPackagesCount,
            'requiredPackagesCount' => $requiredPackagesCount,
            'statesCount' => $statesCount,
            'requiredStatesCount' => $requiredStatesCount,
        ];

        $project->setMetadata($metadata);

        $composer = json_decode(json_decode($project->getComposer()));

        if ($composer->description) {
            $project->setDescription($composer->description);
        }
        if ($composer->authors) {
            $authors = [];
            foreach ($composer->authors as $author) {
                array_push($authors, $author->name . ' <' . $author->email . '> (' . $author->role . ')');
            }
            $project->setAuthors(implode(',', $authors));

        }

        return $project;
    }

    /**
     * @param $dependencyData
     * @param Project $project
     * @param Dependency $dependency
     * @return Dependency
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function processDependency($dependencyData, $project, $dependency) {
        $dependency->setName($dependencyData->name);
        $dependency->setVersion($dependencyData->version);

        // Workaround for adding requirement information to dependencies
        foreach (json_decode(json_decode($project->getComposer()))->require as $name => $require) {
            if ($dependency->getName() == $name) {
                $dependency->setRequired($require);
            }
        }

        // Is dependency outdated?
        if (isset($dependencyData->latest)) {
            $dependency->setLatest($dependencyData->latest);

            $dependency->setState(VersionHelper::compareVersions($dependency->getVersion(), $dependency->getLatest(), $dependency->getRequired()));
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

    /**
     * @param $dependencies
     * @return array
     */
    public static function groupDependencies($dependencies) {

        $projects = [];
        foreach ($dependencies as $dependency) {
            /* @var Dependency $dependency */

            if (!in_array($dependency->getProject(),$projects)) {
                $project = $dependency->getProject();
                /* @var Project $project */
                $project->setDependencies(new ArrayCollection());
                array_push($projects, $project);
            }

            $projects[array_search($dependency->getProject(), $projects)]->addDependency($dependency);
        }

        return $projects;
    }

    /**
     *
     */
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