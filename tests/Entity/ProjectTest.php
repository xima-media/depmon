<?php

namespace Xima\DepmonBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Xima\DepmonBundle\Entity\Dependency;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Util\VersionHelper;

class ProjectTest extends TestCase
{

    /**
     * @var Project
     */
    protected $subject = null;


    protected function setUp()
    {
        $this->subject = new Project();
    }

    public function testCanBeInstantiated()
    {
        self::assertInstanceOf(Project::class, $this->subject);
    }

    public function testGetId()
    {
        self::assertSame(
            null,
            $this->subject->getId()
        );
    }

    public function testGetName()
    {
        self::assertSame(
            '',
            $this->subject->getName()
        );
    }

    public function testSetName()
    {
        $this->subject->setName('Name');

        self::assertSame(
            'Name',
            $this->subject->getName()
        );
    }

    public function testGetState()
    {
        self::assertSame(
            null,
            $this->subject->getState()
        );
    }

    public function testSetState()
    {
        $this->subject->setState(VersionHelper::STATE_UP_TO_DATE);

        self::assertEquals(
            VersionHelper::STATE_UP_TO_DATE,
            $this->subject->getState()
        );
    }

    public function testGetVersion()
    {
        self::assertSame(
            '',
            $this->subject->getVersion()
        );
    }

    public function testSetVersion()
    {
        $this->subject->setVersion('Version');

        self::assertEquals(
            'Version',
            $this->subject->getVersion()
        );
    }

    public function testGetUpdated()
    {
        self::assertSame(
            null,
            $this->subject->getUpdated()
        );
    }

    public function testSetUpdated()
    {
        $this->subject->setUpdated(new \DateTime('today'));

        self::assertEquals(
            new \DateTime('today'),
            $this->subject->getUpdated()
        );
    }

    public function testGetDescription()
    {
        self::assertSame(
            '',
            $this->subject->getDescription()
        );

    }

    public function testSetDescription()
    {
        $this->subject->setDescription('Description');

        self::assertEquals(
            'Description',
            $this->subject->getDescription()
        );

    }

    public function testGetAuthors()
    {
        self::assertSame(
            '',
            $this->subject->getAuthors()
        );
    }

    public function testSetAuthors()
    {
        $this->subject->setAuthors('Author');

        self::assertEquals(
            'Author',
            $this->subject->getAuthors()
        );
    }

    public function testGetComposer()
    {
        self::assertSame(
            '',
            $this->subject->getComposer()
        );
    }

    public function testSetComposer()
    {
        $this->subject->setComposer('Composer');

        self::assertEquals(
            'Composer',
            $this->subject->getComposer()
        );
    }

    public function testGetGit()
    {
        self::assertSame(
            '',
            $this->subject->getGit()
        );
    }

    public function testSetGit()
    {
        $this->subject->setGit('Git');

        self::assertEquals(
            'Git',
            $this->subject->getGit()
        );
    }

    public function testGetPath()
    {
        self::assertSame(
            '',
            $this->subject->getPath()
        );
    }

    public function testSetPath()
    {
        $this->subject->setPath('Path');

        self::assertEquals(
            'Path',
            $this->subject->getPath()
        );
    }

    public function testGetDependencies()
    {
        self::assertSame(
            0,
            $this->subject->getDependencies()->count()
        );
    }

    public function testSetDependencies()
    {
        $newDependency = new Dependency();
        $dependencies = new ArrayCollection();
        $dependencies->add($newDependency);

        $this->subject->setDependencies($dependencies);

        self::assertEquals(
            $dependencies,
            $this->subject->getDependencies()
        );
    }

    public function testAddDependency()
    {
        $newDependency = new Dependency();
        $dependencies = new ArrayCollection();
        $dependencies->add($newDependency);
        $this->subject->addDependency($newDependency);

        self::assertEquals(
            $dependencies,
            $this->subject->getDependencies()
        );
    }

    public function test__toString()
    {
        $this->subject->setName('Name');

        self::assertSame(
            'Name',
            $this->subject->__toString()
        );
    }
}
