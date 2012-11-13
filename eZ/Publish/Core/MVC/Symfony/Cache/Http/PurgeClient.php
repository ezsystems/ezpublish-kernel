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
     * Triggers the cache purge $cacheElements.
     *
     * @param mixed $locationIds Cache resource(s) to purge (array of locationId to purge in the reverse proxy)
     * @return void
     */
    public function purge( $locationIds )
    {
        if ( empty( $locationIds ) )
            return;

        if ( !is_array( $locationIds ) )
            $locationIds = array( $locationIds );

        // Purging all HTTP gateways
        foreach ($this->purgeServers as $server)
        {
            $this->doPurge( $server, $locationIds );

            $client = $this->httpBrowser->getClient();
            if ( $client instanceof BatchClientInterface )
                $client->flush();
        }
    }

    /**
     * Effectively triggers the purge.
     * Sends one HTTP PURGE request per location Id.
     * Used request header is X-Location-Id.
     *
     * @param string $server Current purge server (e.g. http://localhost/foo/bar)
     * @param array $locationIds Location Ids to purge
     * @return void
     */
    protected function doPurge( $server, array $locationIds )
    {
        foreach ( $locationIds as $locationId )
        {
            $this->httpBrowser->call( $server, 'PURGE', array( 'X-Location-Id' => $locationId ) );
        }
    }

    /**
     * Purges all content elements currently in cache.
     *
     * @return void
     */
    public function purgeAll()
    {
        foreach ( $this->purgeServers as $server )
        {
            $this->httpBrowser->call( $server, 'PURGE', array( 'X-Location-Id' => '*' ) );

            $client = $this->httpBrowser->getClient();
            if ( $client instanceof BatchClientInterface )
                $client->flush();
        }
    }
}
