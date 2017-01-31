<?php

/**
 * File containing the Cache PurgeClientInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache;

interface PurgeClientInterface
{
    /**
     * Triggers the cache purge $locationIds.
     *
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     *
     * @param array $tags Cache tags to purge. Ex: location-123,
     *        Since version 6.8 and until 7.0, integers or array of integers will be converted to location tags
     *        (example: [2] => ['location-2']
     *        In 7.x, only tags will be accepted.
     */
    public function purge($tags);

    /**
     * Purges all content elements currently in cache.
     *
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     *
     * @deprecated Since 6.8, use cache:clear, with multi tagging theoretically there shouldn't be need to delete all anymore from core.
     */
    public function purgeAll();
}
