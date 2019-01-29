<?php

namespace Xima\DepmonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Entity(repositoryClass="Xima\DepmonBundle\Repository\DependencyRepository")
 */
class Dependency
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $required;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $stable;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $latest;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="Xima\DepmonBundle\Entity\Project", inversedBy="dependencies")
     */
    private $project;

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRequired(): ?string
    {
        return $this->required;
    }

    public function setRequired(string $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getStable(): ?string
    {
        return $this->stable;
    }

    public function setStable(string $stable): self
    {
        $this->stable = $stable;

        return $this;
    }

    public function getLatest(): ?string
    {
        return $this->latest;
    }

    public function setLatest(string $latest): self
    {
        $this->latest = $latest;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }
}
