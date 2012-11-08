<?php
/**
 * File containing the LocationAwareStore class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Filesystem\Filesystem;

/**
 * Store implements all the logic for storing cache metadata (Request and Response headers).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LocationAwareStore extends Store implements RequestAwarePurger
{
    const LOCATION_CACHE_DIR = 'ezlocation',
          LOCATION_STALE_CACHE_DIR = 'ezlocation_stale';

    /**
     * Injects eZ Publish specific information in the content digest if needed.
     * X-Location-Id response header is set in the ViewController
     *
     * @see \eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController::viewLocation()
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    protected function generateContentDigest( Response $response )
    {
        $digest = parent::generateContentDigest( $response );
        if ( !$response->headers->has( 'X-Location-Id' ) )
        {
            return $digest;
        }

        return static::LOCATION_CACHE_DIR . "/{$response->headers->get( 'X-Location-Id' )}/$digest";
    }

    /**
     * Returns the right path where cache is being stored.
     * Will detect if $key is eZ Publish specific.
     *
     * @param string $key
     * @return string
     */
    public function getPath( $key )
    {
        if ( strpos( $key, static::LOCATION_CACHE_DIR ) === false )
            return parent::getPath( $key );

        $prefix = '';
        if ( ( $pos = strrpos( $key, '/' ) ) !== false )
        {
            $prefix = substr( $key, 0, $pos ) . DIRECTORY_SEPARATOR;
            $key = substr( $key, $pos + 1 );

            // If cache purge is in progress, service stale cache instead of regular cache
            list( $locationCacheDir, $locationId ) = explode( '/', $prefix );
            if ( is_file( $this->getLocationCacheLockName( $locationId ) ) )
            {
                $prefix = str_replace( static::LOCATION_CACHE_DIR, static::LOCATION_STALE_CACHE_DIR, $prefix );
            }
        }


        return $this->root . DIRECTORY_SEPARATOR . $prefix .
           substr( $key, 0, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 2, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 4, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 6 )
        ;
    }

    /**
     * Purges data from $request.
     * If X-Location-Id header is present, the store will purge cache for given locationId.
     * If not, regular purge by URI will occur.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool True if purge was successful. False otherwise
     */
    public function purgeByRequest( Request $request )
    {
        if ( !$request->headers->has( 'X-Location-Id' ) && !$request->headers->has( 'X-Group-Location-Id' ) )
        {
            return $this->purge( $request->getUri() );
        }

        if ( $request->headers->has( 'X-Group-Location-Id' ) )
            $aLocationId = explode( '; ', $request->headers->get( 'X-Group-Location-Id' ) );
        else
            $aLocationId = array( $request->headers->get( 'X-Location-Id' ) );

        if ( empty( $aLocationId ) )
            return false;

        foreach ( $aLocationId as $locationId )
        {
            $locationCacheDir = "$this->root/" . static::LOCATION_CACHE_DIR . "/$locationId";
            if ( file_exists( $locationCacheDir ) )
            {
                // 1. Copy cache files to stale cache dir
                // 2. Place a lock file indicating to use the stale cache
                // 3. Remove real cache dir
                // 4. Remove lock file
                // 5. Remove stale cache dir
                // Note that there is no need to remove the meta-file
                $staleCacheDir = str_replace( static::LOCATION_CACHE_DIR, static::LOCATION_STALE_CACHE_DIR, $locationCacheDir );
                $fs = new Filesystem();
                $fs->mkdir( $staleCacheDir );
                $fs->mirror( $locationCacheDir, $staleCacheDir );
                $lockFile = $this->getLocationCacheLockName( $locationId );
                $fs->touch( $lockFile );
                // array of removal is in reverse order on purpose since remove() starts from the end.
                $fs->remove( array( $staleCacheDir, $lockFile, $locationCacheDir ) );
            }
        }

        return true;
    }

    /**
     * Returns cache lock name for $locationId.
     *
     * @param int $locationId
     * @return string
     */
    private function getLocationCacheLockName( $locationId )
    {
        return "$this->root/_ezloc_$locationId.purging";
    }
}
