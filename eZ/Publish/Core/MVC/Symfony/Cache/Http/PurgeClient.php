<?php
/**
 * File containing the HttpCache purge client class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeInterface,
    Buzz\Browser,
    Buzz\Client\BatchClientInterface;

class PurgeClient implements PurgeInterface
{
    /**
     * Array of URIs to be purged
     *
     * @var string[]
     */
    protected $urlsToPurge;

    /**
     * @var \Buzz\Browser
     */
    protected $httpBrowser;

    public function __construct( Browser $httpBrowser )
    {
        $this->httpBrowser = $httpBrowser;
    }

    /**
     * Sets the cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     *
     * @param array $urlsToPurge
     * @return void
     */
    public function setCacheElements( $urlsToPurge )
    {
        if ( !is_array( $urlsToPurge ) )
        {
            $urlsToPurge = array( $urlsToPurge );
        }

        $this->urlsToPurge = $urlsToPurge;
    }

    /**
     * Triggers the cache purge of the elements registered via {@link PurgeInterface::setCacheElements}
     *
     * @return void
     */
    public function purge()
    {
        if ( empty( $this->urlsToPurge ) )
            return;

        foreach ( $this->urlsToPurge as $url )
        {
            $this->httpBrowser->call( $url, 'PURGE' );
        }

        $client = $this->httpBrowser->getClient();
        if ( $client instanceof BatchClientInterface )
            $client->flush();
    }
}
