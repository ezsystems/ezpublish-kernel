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
     * Triggers the cache purge of given $locationIds.
     * It's up to the implementor to decide whether to purge $locationIds right away or to delegate to a separate process.
     *
     * @deprecated as of 6.0. Will be removed in 7.0. Use purgeForContent() instead.
     *
     * @param array $locationIds
     *
     * @return mixed
     */
    public function purge($locationIds);

    /**
     * Triggers cache purge for given content.
     *
     * If given content has several locations, cache will be purged for all of them, including locations provided by
     * caller, which is needed in cases content has been removed from locations.
     *
     * This method can not be used for deleted content, in that case instead use purge(), or in worst case purgeAll().
     *
     * @param mixed $contentId Content ID.
     * @param array $locationIds
     */
    public function purgeForContent($contentId, $locationIds = []);

    /**
     * Triggers the cache purge for all content in cache.
     *
     * @return mixed
     */
    public function purgeAll();
}
