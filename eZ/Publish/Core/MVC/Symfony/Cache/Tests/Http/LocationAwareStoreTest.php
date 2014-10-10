<?php
/**
 * File containing the LocationAwareStoreTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\CacheTests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\LocationAwareStore;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_TestCase;

class LocationAwareStoreTest extends PHPUnit_Framework_TestCase
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

    public function testGetFilesystem()
    {
        $this->assertInstanceOf( 'Symfony\\Component\\Filesystem\\Filesystem', $this->store->getFilesystem() );
    }

    public function testSetFilesystem()
    {
        $fs = new Filesystem();
        $this->store->setFilesystem( $fs );
        $this->assertSame( $fs, $this->store->getFilesystem() );
    }

    public function testGetPath()
    {
        $prefix = LocationAwareStore::LOCATION_CACHE_DIR . '/123/';
        $path = $this->store->getPath( "$prefix/en" . sha1( 'someContent' ) );
        $this->assertTrue( strpos( $path, __DIR__ . "/$prefix" ) === 0 );
    }

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
        $request->headers->set( 'X-Location-Id', "$locationId" );
        $this->store->purgeByRequest( $request );
    }

    public function testPurgeByRequestMultipleLocationsBC()
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

        $request = Request::create( '/', 'BAN' );
        $request->headers->set( 'X-Location-Id', '(' . implode( '|', $locationIds ) . ')' );
        $this->store->purgeByRequest( $request );
    }

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

        $request = Request::create( '/', 'BAN' );
        $request->headers->set( 'X-Location-Id', '.*' );
        $this->store->purgeByRequest( $request );
    }

    public function testPurgeAllContentByRequestBC()
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
