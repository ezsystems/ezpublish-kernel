<?php

/**
 * File containing the Cache PurgeClientInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache;

/**
 * A purge client able to purge from a list of tags.
 */
interface TagAwarePurgeClientInterface
{
    /**
     * Triggers the cache purge $locationIds.
     *
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     *
     * @deprecate Since 6.8, use {@link purgeByTags()}
     *
     * @param array $locationIds Cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     */
    public function purge($locationIds);

    /**
     * Triggers the cache purge/invalidation of cache by $tags.
     *
     * See doc/specifications/cache/multi_tagging.md for list of system tags and conventions.
     *
     * @since 6.8
     *
     * @param string[] $tags Tags that
     */
    public function purgeByTags(array $tags);

    /**
     * Purges all content elements currently in cache.
     *
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     *
     * @deprecated Use cache:clear, with multi tagging theoretically there shouldn't be need to delete all anymore from core.
     */
    public function purgeAll();
}
