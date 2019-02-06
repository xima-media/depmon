<?php

namespace Xima\DepmonBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Xima\DepmonBundle\Entity\Dependency;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Util\VersionHelper;

class DependencyTest extends TestCase
{

    /**
     * @var Dependency
     */
    protected $subject = null;


    protected function setUp()
    {
        $this->subject = new Dependency();
    }

    public function testCanBeInstantiated()
    {
        self::assertInstanceOf(Dependency::class, $this->subject);
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

    public function testGetRequired()
    {
        self::assertSame(
            '',
            $this->subject->getName()
        );
    }

    public function testSetRequired()
    {
        $this->subject->setRequired('Required');

        self::assertSame(
            'Required',
            $this->subject->getRequired()
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

    public function testGetLatest()
    {
        self::assertSame(
            '',
            $this->subject->getLatest()
        );
    }

    public function testSetLatest()
    {
        $this->subject->setLatest('Latest');

        self::assertSame(
            'Latest',
            $this->subject->getLatest()
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

    public function testGetLink()
    {
        self::assertSame(
            '',
            $this->subject->getLink()
        );
    }

    public function testSetLink()
    {
        $this->subject->setLink('Link');

        self::assertEquals(
            'Link',
            $this->subject->getLink()
        );
    }

    public function testGetProject()
    {
        self::assertSame(
            null,
            $this->subject->getProject()
        );
    }

    public function testSetProject()
    {
        $newProject = new Project();
        $this->subject->setProject($newProject);

        self::assertEquals(
            $newProject,
            $this->subject->getProject()
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
