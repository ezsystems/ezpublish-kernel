<?php
/**
 * File containing the GatewayCachePurger interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache;

/**
 * Interface for gateway cache purgers.
 */
interface GatewayCachePurger
{
    /**
     * Triggers the cache purge of given $cacheElements.
     * It's up to the implementor to decide whether to purge $cacheElements right away or to delegate to a separate process.
     *
     * @param mixed $cacheElements
     *
     * @return mixed
     */
    public function purge( $cacheElements );

    /**
     * Triggers the cache purge for all content in cache.
     *
     * @return mixed
     */
    public function purgeAll();
}
