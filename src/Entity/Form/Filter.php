<?php

namespace Xima\DepmonBundle\Entity\Form;

/**
 * Class Filter
 * @package Xima\DepmonBundle\Entity\Form
 */
class Filter
{

    /**
     * @var string
     */
    private $dependencySearch;

    /**
     * @var array
     */
    private $projects = [];

    /**
     * @var bool
     */
    private $showOverview = false;

    /**
     * @var bool
     */
    private $showIndirectlyDependencies = false;

    /**
     * @var array
     */
    private $states = [];


    public function __construct()
    {

    }

    /**
     * @return string|null
     */
    public function getDependencySearch(): ?string
    {
        return $this->dependencySearch;
    }

    /**
     * @param string $dependencySearch
     */
    public function setDependencySearch(string $dependencySearch): void
    {
        $this->dependencySearch = $dependencySearch;
    }

    /**
     * @return array
     */
    public function getProjects(): array
    {
        return $this->projects;
    }

    /**
     * @param array $projects
     */
    public function setProjects(array $projects): void
    {
        $this->projects = $projects;
    }

    /**
     * @return bool|null
     */
    public function isShowOverview(): ?bool
    {
        return $this->showOverview;
    }

    /**
     * @param bool $showOverview
     */
    public function setShowOverview(bool $showOverview): void
    {
        $this->showOverview = $showOverview;
    }

    /**
     * @return array|null
     */
    public function getStates(): ?array
    {
        return $this->states;
    }

    /**
     * @param array $states
     */
    public function setStates(array $states): void
    {
        $this->states = $states;
    }

    /**
     * @return bool
     */
    public function isShowIndirectlyDependencies(): bool
    {
        return $this->showIndirectlyDependencies;
    }

    /**
     * @param bool $showDependentDependencies
     */
    public function setShowIndirectlyDependencies(bool $showIndirectlyDependencies): void
    {
        $this->showIndirectlyDependencies = $showIndirectlyDependencies;
    }

}