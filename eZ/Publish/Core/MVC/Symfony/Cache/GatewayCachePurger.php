<?php

/**
 * File containing the GatewayCachePurger interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache;

/**
 * Interface for gateway cache purgers, i.e. Http cache purgers.
 */
interface GatewayCachePurger
{
    /**
     * Triggers the cache purge of given $cacheElements.
     * It's up to the implementor to decide whether to purge $cacheElements right away or to delegate to a separate process.
     *
     * @deprecated as of 6.0. Will be removed in 6.1. Use purgeForContent() instead.
     *
     * @param mixed $cacheElements
     *
     * @return mixed
     */
    public function purge($cacheElements);

    /**
     * Triggers cache purge for given content.
     * If given content has several locations, cache will be purged for all of them.
     *
     * @param mixed $contentId Content ID.
     */
    public function purgeForContent($contentId);

    /**
     * Triggers the cache purge for all content in cache.
     *
     * @return mixed
     */
    public function purgeAll();
}
