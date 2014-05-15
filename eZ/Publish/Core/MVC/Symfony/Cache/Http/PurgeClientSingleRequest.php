<?php
/**
 * File containing the PurgeClientSingleRequest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

class PurgeClientSingleRequest extends PurgeClient
{
    /**
     * Effectively triggers the purge.
     * Sends one HTTP PURGE request for all location Ids.
     * Used request header is X-Group-Location-Id instead of X-Location-Id.
     *
     * @param string $server
     * @param array $locationIds
     */
    protected function doPurge( $server, array $locationIds )
    {
        $this->httpBrowser->call(
            $server,
            'PURGE',
            array(
                'X-Group-Location-Id' => implode( '; ', $locationIds )
            )
        );
    }
}
