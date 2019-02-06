<?php

namespace Xima\DepmonBundle\Tests\Entity\Form;

use Xima\DepmonBundle\Entity\Form\Filter;
use PHPUnit\Framework\TestCase;
use Xima\DepmonBundle\Util\VersionHelper;

class FilterTest extends TestCase
{

    /**
     * @var Filter
     */
    protected $subject = null;


    protected function setUp()
    {
        $this->subject = new Filter();
    }

    public function testCanBeInstantiated()
    {
        self::assertInstanceOf(Filter::class, $this->subject);
    }

    public function testGetDependencySearch()
    {
        self::assertSame(
            null,
            $this->subject->getDependencySearch()
        );
    }

    public function testSetDependencySearch()
    {
        $this->subject->setDependencySearch('Search');

        self::assertSame(
            'Search',
            $this->subject->getDependencySearch()
        );
    }

    public function testGetProjects()
    {
        self::assertSame(
            0,
            count($this->subject->getProjects())
        );
    }

    public function testSetProjects()
    {
        $projects = [1];
        $this->subject->setProjects($projects);

        self::assertSame(
            $projects,
            $this->subject->getProjects()
        );
    }

    public function testIsShowOverview()
    {
        self::assertSame(
            false,
            $this->subject->isShowOverview()
        );
    }

    public function testSetShowOverview()
    {
        $this->subject->setShowOverview(true);

        self::assertSame(
            true,
            $this->subject->isShowOverview()
        );
    }

    public function testIsShowIndirectlyDependencies()
    {
        self::assertSame(
            false,
            $this->subject->isShowIndirectlyDependencies()
        );
    }

    public function testSetShowIndirectlyDependencies()
    {
        $this->subject->setShowIndirectlyDependencies(true);

        self::assertSame(
            true,
            $this->subject->isShowIndirectlyDependencies()
        );
    }

    public function testGetStates()
    {
        self::assertSame(
            0,
            count($this->subject->getStates())
        );
    }

    public function testSetStates()
    {
        $states = [VersionHelper::STATE_UP_TO_DATE];
        $this->subject->setStates($states);

        self::assertSame(
            $states,
            $this->subject->getStates()
        );
    }
}
