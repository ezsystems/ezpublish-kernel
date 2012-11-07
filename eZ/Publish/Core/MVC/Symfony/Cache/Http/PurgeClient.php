<?php
/**
 * File containing the HttpCache purge client class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface,
    eZ\Publish\Core\MVC\ConfigResolverInterface,
    Buzz\Browser,
    Buzz\Client\BatchClientInterface;

class PurgeClient implements PurgeClientInterface
{
    /**
     * Array of URIs to be purged
     *
     * @var string[]
     */
    protected $locationIds;

    /**
     * Servers that will be used to purge HTTP cache.
     * Each server consists in a full URL (e.g. http://localhost/foo/bar)
     *
     * @var mixed
     */
    protected $purgeServers;

    /**
     * @var \Buzz\Browser
     */
    protected $httpBrowser;

    public function __construct( ConfigResolverInterface $configResolver, Browser $httpBrowser )
    {
        $this->httpBrowser = $httpBrowser;
        $this->purgeServers = $configResolver->getParameter( 'http_cache.purge_servers' );
    }

    /**
     * Sets the cache resource(s) to purge (e.g. array of URI to purge in a reverse proxy)
     *
     * @param array $locationIds
     * @return void
     */
    public function setCacheElements( $locationIds )
    {
        if ( !is_array( $locationIds ) )
        {
            $locationIds = array( $locationIds );
        }

        $this->locationIds = $locationIds;
    }

    /**
     * Triggers the cache purge of the elements registered via {@link PurgeClientInterface::setCacheElements}
     *
     * @return void
     */
    public function purge()
    {
        if ( empty( $this->locationIds ) )
            return;

        // Purging all HTTP gateways
        foreach ($this->purgeServers as $server)
        {
            foreach ( $this->locationIds as $locationId )
            {
                $this->httpBrowser->call( $server, 'PURGE', array( 'X-Location-Id' => $locationId ) );
            }

            $client = $this->httpBrowser->getClient();
            if ( $client instanceof BatchClientInterface )
                $client->flush();
        }
    }
}
