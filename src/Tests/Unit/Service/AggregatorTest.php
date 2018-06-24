<?php

namespace Xima\DepmonBundle\Tests\Unit\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Xima\DepmonBundle\Service\Aggregator;

class AggregatorTest extends WebTestCase
{

    /**
     * @var Aggregator
     */
    private $subject = null;

    protected function setUp()
    {
        self::bootKernel();

        // returns the real and unchanged service container
        $container = self::$kernel->getContainer();

        // returns the special container that allows fetching private services
        $client = static::createClient();
        $container = $client->getContainer();
        // alternative way to get the special container:
        // $container = self::$container;

        $subject = self::$container->get('xima_depmon.aggregator');
    }

    public static function setUpBeforeClass()
    {

    }
}