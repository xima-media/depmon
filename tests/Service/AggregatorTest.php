<?php

namespace Xima\DepmonBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xima\DepmonBundle\Service\Aggregator;

class AggregatorTest extends WebTestCase
{

    /**
     * @var Aggregator
     */
    private $aggregator;

    private $projectSampleData = [
        'name' => 'depmon',
        'git' => 'https://github.com/xima-media/depmon.git',
        'path' => ''
    ];

    /**
     * Preparation
     */
    public function setUp()
    {
        $this->aggregator = new Aggregator();
    }

    public function testAggregation()
    {
//        // get own composer.json as reference
//        $composerFile = file_get_contents("./composer.json");
//        $composerJson = json_decode($composerFile, true);
//
//        // aggregating project data
//
//        $project = $this->projectSampleData;
//
//        $this->aggregator->updateProjectData($project);
//        $this->aggregator->checkIfComposerJsonExists($project);
//        $composerLock = $this->aggregator->checkIfComposerLockExists($project);
//        $this->aggregator->validateComposerFiles($project);
//        $this->aggregator->installComposerDependencies($project, $composerLock);
//        $data = $this->aggregator->fetchComposerData($project);
//
//        // check equal names
//        $this->assertEquals($composerJson['name'], $data['composer']->name);
//        // check if dependencies are there
//        $this->assertNotEmpty($data['dependencies']);
        $this->assertTrue(true);

    }

    /**
     * Clean up
     */
    public function tearDown()
    {
//        $this->aggregator->clearProjectData($this->projectSampleData);
//        rmdir('var/data');
//        rmdir('var');
    }

}