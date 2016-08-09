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
     *
     * @deprecated as of 6.0, might be removed in a future major version. Use purgeForContent() instead when content exist.
     *
     * @param array $locationIds
     *
     * @return mixed
     */
    public function purge($locationIds);

    /**
     * Purge Content cache using $locationIds and gather additional relevant cache to clear based on $contentId.
     *
     * @deprecated in 6.5, design flaw on deleted/trashed content, use purge() when content does not exist for now.
     *             See EZP-25696 for potential future feature to solve this.
     *
     * @param mixed $contentId Content ID.
     * @param array $locationIds Initial location id's from signal to take into account.
     */
    public function purgeForContent($contentId, $locationIds = []);

    /**
     * Triggers the cache purge for all content in cache.
     *
     * @return mixed
     */
    public function purgeAll();
}
