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
     * Triggers the cache purge/invalidation of cache by $tags.
     *
     * See doc/specifications/cache/multi_tagging.md for list of system tags and conventions.
     *
     * @since 6.8
     *
     * @param string[] $tags Tags that
     */
    public function purgeByTags(array $tags);
}
