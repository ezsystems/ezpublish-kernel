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
    Symfony\Component\Filesystem\Filesystem,
    Symfony\Component\Filesystem\Exception\IOException;

/**
 * LocationAwareStore implements all the logic for storing cache metadata regarding locations.
 */
class LocationAwareStore extends Store implements ContentPurger
{
    const LOCATION_CACHE_DIR = 'ezlocation',
          LOCATION_STALE_CACHE_DIR = 'ezlocation_stale';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $fs;

    /**
     * Injects a Filesystem instance
     * For unit tests only.
     *
     * @internal
     *
     * @param \Symfony\Component\Filesystem\Filesystem $fs
     */
    public function setFilesystem( Filesystem $fs )
    {
        $this->fs = $fs;
    }

    /**
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        if ( !isset( $this->fs ) )
            $this->fs = new Filesystem();

        return $this->fs;
    }

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

            list( $locationCacheDir, $locationId ) = explode( '/', $prefix );
            // If cache purge is in progress, serve stale cache instead of regular cache.
            // We first check for a global cache purge, then for the current location.
            foreach ( array( $this->getLocationCacheLockName(), $this->getLocationCacheLockName( $locationId ) ) as $cacheLockFile )
            {
                if ( is_file( $cacheLockFile ) )
                {
                    if ( function_exists( 'posix_kill' ) )
                    {
                        // Check if purge process is still running. If not, remove the lock file to unblock future cache purge
                        if ( !posix_kill( file_get_contents( $cacheLockFile ), 0 ) )
                        {
                            $fs = $this->getFilesystem();
                            $fs->remove( array( $cacheLockFile, $this->getLocationCacheDir( $locationId ) ) );
                            goto returnCachePath;
                        }
                    }

                    $prefix = str_replace( static::LOCATION_CACHE_DIR, static::LOCATION_STALE_CACHE_DIR, $prefix );
                }
            }
        }

        returnCachePath:
        return $this->root . DIRECTORY_SEPARATOR . $prefix .
           substr( $key, 0, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 2, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 4, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 6 )
        ;
    }

    /**
     * Purges data from $request.
     * If X-Location-Id or X-Group-Location-Id header is present, the store will purge cache for given locationId or group of locationIds.
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

        // Purge everything
        if ( $request->headers->get( 'X-Location-Id' ) === '*' )
        {
            return $this->purgeAllContent();
        }

        if ( $request->headers->has( 'X-Group-Location-Id' ) )
            $aLocationId = explode( '; ', $request->headers->get( 'X-Group-Location-Id' ) );
        else
            $aLocationId = array( $request->headers->get( 'X-Location-Id' ) );

        if ( empty( $aLocationId ) )
            return false;

        foreach ( $aLocationId as $locationId )
        {
            $this->purgeLocation( $locationId );
        }

        return true;
    }

    /**
     * Purges all cached content
     *
     * @return bool
     */
    public function purgeAllContent()
    {
        return $this->purgeLocation( null );
    }

    /**
     * Purges cache for $locationId
     *
     * @param int|null $locationId. If null, all locations will be purged.
     * @return bool
     */
    private function purgeLocation( $locationId )
    {
        $fs = $this->getFilesystem();
        $locationCacheDir = $this->getLocationCacheDir( $locationId );
        if ( $fs->exists( $locationCacheDir ) )
        {
            // 1. Copy cache files to stale cache dir
            // 2. Place a lock file indicating to use the stale cache
            // 3. Remove real cache dir
            // 4. Remove lock file
            // 5. Remove stale cache dir
            // Note that there is no need to remove the meta-file
            $staleCacheDir = str_replace( static::LOCATION_CACHE_DIR, static::LOCATION_STALE_CACHE_DIR, $locationCacheDir );
            $fs->mkdir( $staleCacheDir );
            $fs->mirror( $locationCacheDir, $staleCacheDir );
            $lockFile = $this->getLocationCacheLockName( $locationId );
            file_put_contents( $lockFile, getmypid() );
            try
            {
                // array of removal is in reverse order on purpose since remove() starts from the end.
                $fs->remove( array( $staleCacheDir, $lockFile, $locationCacheDir ) );
                return true;
            }
            catch ( IOException $e )
            {
                // Log the error in the standard error log and at least try to remove the lock file
                error_log( $e->getMessage() );
                @unlink( $lockFile );
                return false;
            }
        }

        return false;
    }

    /**
     * Returns cache lock name for $locationId.
     *
     * This method is public only for unit tests.
     * Use it only if you know what you are doing.
     *
     * @internal
     *
     * @param int $locationId. If null, will return a global cache lock name (purging all content)
     * @return string
     */
    public function getLocationCacheLockName( $locationId = null )
    {
        $locationId = $locationId ?: 'all';
        return "$this->root/_ezloc_$locationId.purging";
    }

    /**
     * Returns cache dir for $locationId.
     *
     * This method is public only for unit tests.
     * Use it only if you know what you are doing.
     *
     * @internal
     *
     * @param int $locationId
     * @return string
     */
    public function getLocationCacheDir( $locationId = null )
    {
        $cacheDir = "$this->root/" . static::LOCATION_CACHE_DIR;
        if ( $locationId )
            $cacheDir .= "/$locationId";

        return $cacheDir;
    }
}
