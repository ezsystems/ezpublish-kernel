<?php
/**
 * File containing the Cache PurgeClientInterface class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache;

interface PurgeClientInterface
{
    /**
     * Sets the cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     *
     * @param mixed $cacheElements
     * @return void
     */
    public function setCacheElements( $cacheElements );

    /**
     * Triggers the cache purge of the elements registered via {@link PurgeClientInterface::setCacheElements}
     *
     * @return void
     */
    public function purge();
}
