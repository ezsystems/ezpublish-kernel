<?php
/**
 * File containing the LocationAwareStoreTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\CacheTests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class LocationAwareStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore
     */
    private $store;

    protected function setUp()
    {
        parent::setUp();
        $this->store = new LocationAwareStore( __DIR__ );
    }

    protected function tearDown()
    {
        array_map( 'unlink', glob( __DIR__ . '/*.purging' ) );
        parent::tearDown();
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getFilesystem
     */
    public function testGetFilesystem()
    {
        $this->assertInstanceOf( 'Symfony\\Component\\Filesystem\\Filesystem', $this->store->getFilesystem() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::setFilesystem
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getFilesystem
     */
    public function testSetFilesystem()
    {
        $fs = new Filesystem();
        $this->store->setFilesystem( $fs );
        $this->assertSame( $fs, $this->store->getFilesystem() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getPath
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     */
    public function testGetPath()
    {
        $prefix = LocationAwareStore::LOCATION_CACHE_DIR . '/123/';
        $path = $this->store->getPath( "$prefix/en" . sha1( 'someContent' ) );
        $this->assertTrue( strpos( $path, __DIR__ . "/$prefix" ) === 0 );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getPath
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     */
    public function testGetStalePath()
    {
        // Generate the lock file to force using the stale cache dir
        $locationId = 123;
        $prefix = LocationAwareStore::LOCATION_CACHE_DIR . "/$locationId";
        $prefixStale = LocationAwareStore::LOCATION_STALE_CACHE_DIR . "/$locationId";
        $lockFile = $this->store->getLocationCacheLockName( $locationId );
        file_put_contents( $lockFile, getmypid() );

        $path = $this->store->getPath( "$prefix/en" . sha1( 'someContent' ) );
        $this->assertTrue( strpos( $path, __DIR__ . "/$prefixStale" ) === 0 );
        @unlink( $lockFile );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getPath
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     */
    public function testGetPathDeadProcess()
    {
        if ( !function_exists( 'posix_kill' ) )
            self::markTestSkipped( 'posix_kill() function is needed for this test' );

        $locationId = 123;
        $prefix = LocationAwareStore::LOCATION_CACHE_DIR . "/$locationId";
        $lockFile = $this->store->getLocationCacheLockName( $locationId );
        file_put_contents( $lockFile, '99999999999999999' );

        $path = $this->store->getPath( "$prefix/en" . sha1( 'someContent' ) );
        $this->assertTrue( strpos( $path, __DIR__ . "/$prefix" ) === 0 );
        $this->assertFalse( file_exists( $lockFile ) );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFilesystemMock()
    {
        return $this->getMock( 'Symfony\\Component\\Filesystem\\Filesystem' );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeByRequest
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheDir
     */
    public function testPurgeByRequestSingleLocation()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem( $fs );
        $locationId = 123;
        $locationCacheDir = $this->store->getLocationCacheDir( $locationId );
        $staleCacheDir = str_replace( LocationAwareStore::LOCATION_CACHE_DIR, LocationAwareStore::LOCATION_STALE_CACHE_DIR, $locationCacheDir );

        $fs
            ->expects( $this->any() )
            ->method( 'exists' )
            ->with( $locationCacheDir )
            ->will( $this->returnValue( true ) );
        $fs
            ->expects( $this->once() )
            ->method( 'mkdir' )
            ->with( $staleCacheDir );
        $fs
            ->expects( $this->once() )
            ->method( 'mirror' )
            ->with( $locationCacheDir, $staleCacheDir );
        $fs
            ->expects( $this->once() )
            ->method( 'remove' )
            ->with( array( $staleCacheDir, $this->store->getLocationCacheLockName( $locationId ), $locationCacheDir ) );

        $request = Request::create( '/', 'PURGE' );
        $request->headers->set( 'X-Location-Id', $locationId );
        $this->store->purgeByRequest( $request );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeByRequest
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheDir
     */
    public function testPurgeByRequestMultipleLocations()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem( $fs );
        $locationIds = array( 123, 456, 789 );
        $i = 0;
        foreach ( $locationIds as $locationId )
        {
            $locationCacheDir = $this->store->getLocationCacheDir( $locationId );
            $staleCacheDir = str_replace( LocationAwareStore::LOCATION_CACHE_DIR, LocationAwareStore::LOCATION_STALE_CACHE_DIR, $locationCacheDir );

            $fs
                ->expects( $this->at( $i++ ) )
                ->method( 'exists' )
                ->with( $locationCacheDir )
                ->will( $this->returnValue( true ) );
            $fs
                ->expects( $this->at( $i++ ) )
                ->method( 'mkdir' )
                ->with( $staleCacheDir );
            $fs
                ->expects( $this->at( $i++ ) )
                ->method( 'mirror' )
                ->with( $locationCacheDir, $staleCacheDir );
            $fs
                ->expects( $this->at( $i++ ) )
                ->method( 'remove' )
                ->with( array( $staleCacheDir, $this->store->getLocationCacheLockName( $locationId ), $locationCacheDir ) );
        }

        $request = Request::create( '/', 'PURGE' );
        $request->headers->set( 'X-Group-Location-Id', implode( '; ', $locationIds ) );
        $this->store->purgeByRequest( $request );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeAllContent
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheDir
     */
    public function testPurgeAllContent()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem( $fs );
        $locationCacheDir = $this->store->getLocationCacheDir();
        $staleCacheDir = str_replace( LocationAwareStore::LOCATION_CACHE_DIR, LocationAwareStore::LOCATION_STALE_CACHE_DIR, $locationCacheDir );

        $fs
            ->expects( $this->any() )
            ->method( 'exists' )
            ->with( $locationCacheDir )
            ->will( $this->returnValue( true ) );
        $fs
            ->expects( $this->once() )
            ->method( 'mkdir' )
            ->with( $staleCacheDir );
        $fs
            ->expects( $this->once() )
            ->method( 'mirror' )
            ->with( $locationCacheDir, $staleCacheDir );
        $fs
            ->expects( $this->once() )
            ->method( 'remove' )
            ->with( array( $staleCacheDir, $this->store->getLocationCacheLockName(), $locationCacheDir ) );

        $this->store->purgeAllContent();
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeByRequest
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::purgeLocation
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheLockName
     * @covers \eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore::getLocationCacheDir
     */
    public function testPurgeAllContentByRequest()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem( $fs );
        $locationCacheDir = $this->store->getLocationCacheDir();
        $staleCacheDir = str_replace( LocationAwareStore::LOCATION_CACHE_DIR, LocationAwareStore::LOCATION_STALE_CACHE_DIR, $locationCacheDir );

        $fs
            ->expects( $this->any() )
            ->method( 'exists' )
            ->with( $locationCacheDir )
            ->will( $this->returnValue( true ) );
        $fs
            ->expects( $this->once() )
            ->method( 'mkdir' )
            ->with( $staleCacheDir );
        $fs
            ->expects( $this->once() )
            ->method( 'mirror' )
            ->with( $locationCacheDir, $staleCacheDir );
        $fs
            ->expects( $this->once() )
            ->method( 'remove' )
            ->with( array( $staleCacheDir, $this->store->getLocationCacheLockName(), $locationCacheDir ) );

        $request = Request::create( '/', 'PURGE' );
        $request->headers->set( 'X-Location-Id', '*' );
        $this->store->purgeByRequest( $request );
    }
}
