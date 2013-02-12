<?php
/**
 * File containing the Cache PurgeClientInterface class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache;

interface PurgeClientInterface
{
    /**
     * Triggers the cache purge $cacheElements.
     *
     * @param mixed $cacheElements Cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     *
     * @return void
     */
    public function purge( $cacheElements );

    /**
     * Purges all content elements currently in cache.
     *
     * @return void
     */
    public function purgeAll();
}
