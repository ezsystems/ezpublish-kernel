<?php

/**
 * File containing the RedisIgbinary class.
 *
 * @copyright Copyright (c) 2009, Robert Hafner. All rights reserved.
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 *
 * Original source: https://github.com/tedious/Stash/blob/master/src/Stash/Driver/Redis.php
 *
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis;

use Stash\Driver\Redis as StashRedis;

class RedisIgbinary extends StashRedis
{
    public function getData($key)
    {
        $data = $this->redis->get($this->makeKeyString($key));
        if (false !== $data) {
            return igbinary_unserialize($data);
        }

        return $data;
    }

    public function storeData($key, $data, $expiration)
    {
        $store = igbinary_serialize(array('data' => $data, 'expiration' => $expiration));
        if (null === $expiration) {
            return $this->redis->set($this->makeKeyString($key), $store);
        } else {
            $ttl = $expiration - time();

            // Prevent us from even passing a negative ttl'd item to redis,
            // since it will just round up to zero and cache forever.
            if ($ttl < 1) {
                return true;
            }

            return $this->redis->setex($this->makeKeyString($key), $ttl, $store);
        }
    }

    public static function isAvailable()
    {
        return class_exists('Redis', false)
            && extension_loaded('igbinary');
    }
}
