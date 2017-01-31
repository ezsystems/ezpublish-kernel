<?php

/**
 * File containing the FOSClient class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use FOS\HttpCacheBundle\CacheManager;

/**
 * Purge client based on FOSHttpCacheBundle.
 *
 * Only support BAN requests on purpose, to be able to invalidate cache for a
 * collection of Location/Content objects.
 */
class FOSPurgeClient implements PurgeClientInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function __destruct()
    {
        $this->cacheManager->flush();
    }

    public function purge($tags)
    {
        if (empty($tags)) {
            return;
        }

        // As key only support one tag being invalidated at a time, we loop.
        // These will be queued by FOS\HttpCache\ProxyClient\Varnish and handled on kernel.terminate.
        foreach (array_unique((array)$tags) as $tag) {
            if (is_numeric($tag)) {
                $tag = 'location-' . $tag;
            }

            $this->cacheManager->invalidatePath(
                '/',
                ['key' => $tag, 'Host' => empty($_SERVER['SERVER_NAME']) ? 'localhost' : $_SERVER['SERVER_NAME']]
            );
        }
    }

    public function purgeAll()
    {
        $this->cacheManager->invalidate(['key' => '.*']);
    }
}
