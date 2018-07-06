<?php

namespace Xima\DepmonBundle\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Class Cache
 * @package Xima\DepmonBundle\Service
 */
class Cache
{

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    public function __construct($cacheDirectory = null)
    {
        if (!$cacheDirectory) {
            $cacheDirectory = __DIR__ . '/../../../../var/cache/app';
        }

        $this->cache = new FilesystemCache(
            'depmon',
            0,
            $cacheDirectory
        );
    }

    /**
     *
     */
    public function get($key)
    {
        try {
            return $this->cache->get($key);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
        }
    }

    /**
     *
     */
    public function set($key, $value): bool
    {
        try {
            return $this->cache->set($key, $value);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
        }
    }
}