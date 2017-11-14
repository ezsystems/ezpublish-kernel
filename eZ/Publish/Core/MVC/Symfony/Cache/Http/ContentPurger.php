<?php

/**
 * File containing the LocationPurger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

/**
 * Interface allowing for HttpCache stores to purge specific content.
 * When purging content by locationId, purgeByRequest() would receive a Request object with X-Location-Id or X-Group-Location-Id headers
 * indicating which locations to purge.
 *
 * @deprecated since 6.8. The interface should not be necessary anymore.
 */
interface ContentPurger extends RequestAwarePurger
{
    /**
     * Purges all cached content.
     *
     * @return bool
     */
    public function purgeAllContent();
}
