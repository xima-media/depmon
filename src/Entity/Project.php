<?php

namespace Xima\DepmonBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Xima\DepmonBundle\Repository\ProjectRepository")
 */
class Project
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
    private $name = '';

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $version = '';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $authors = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $composer = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $git = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $metadata = '';

    /**
     * @ORM\OneToMany(targetEntity="Xima\DepmonBundle\Entity\Dependency", mappedBy="project", cascade={"all"})
     */
    private $dependencies;

    public function __construct()
    {
        $this->dependencies = new ArrayCollection();
    }

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

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

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

    public function getAuthors(): ?string
    {
        return $this->authors;
    }

    public function setAuthors(?string $authors): self
    {
        $this->authors = $authors;

        return $this;
    }

    /*
     *
     */
    public function getComposer()
    {
        return json_decode($this->composer);
    }

    public function setComposer($composer): self
    {
        $this->composer = json_encode($composer);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGit()
    {
        return $this->git;
    }

    /**
     * @param mixed $git
     */
    public function setGit($git): void
    {
        $this->git = $git;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path): void
    {
        $this->path = $path;
    }

    /**
     * @return Collection|Dependency[]
     */
    public function getDependencies(): Collection
    {
        return $this->dependencies;
    }

    /**
     * @param mixed $dependencies
     */
    public function setDependencies($dependencies): void
    {
        $this->dependencies = $dependencies;
    }


    /**
     * @param Dependency $dependencies
     */
    public function addDependency(Dependency $dependency): void
    {
        $dependency->setProject($this);
        $this->dependencies->add($dependency);
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return json_decode($this->metadata);
    }

    /**
     * @param mixed $metadata
     */
    public function setMetadata($metadata): void
    {
        $this->metadata = json_encode($metadata);
    }
}
