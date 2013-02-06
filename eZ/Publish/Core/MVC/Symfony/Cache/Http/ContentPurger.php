<?php
/**
 * File containing the LocationPurger class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

/**
 * Interface allowing for HttpCache stores to purge specific content.
 * When purging content by locationId, purgeByRequest() would receive a Request object with X-Location-Id or X-Group-Location-Id headers
 * indicating which locations to purge.
 */
interface ContentPurger extends RequestAwarePurger
{
    /**
     * Purges all cached content
     *
     * @return boolean
     */
    public function purgeAllContent();
}
