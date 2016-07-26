<?php

/**
 * File containing the Cache PurgeClientInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache;

interface PurgeClientInterface
{
    /**
     * Triggers the cache purge $locationIds.
     *
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     *
     * @param array $locationIds Cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     */
    public function purge($locationIds);

    /**
     * Purges all content elements currently in cache.
     *
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     */
    public function purgeAll();
}
