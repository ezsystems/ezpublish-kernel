<?php

/**
 * File containing the TagAwareStoreTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\CacheTests\Http;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\Proxy\TagAwareStore;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit_Framework_TestCase;

class TagAwareStoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\Proxy\TagAwareStore
     */
    private $store;

    protected function setUp()
    {
        parent::setUp();
        $this->store = new TagAwareStore(__DIR__);
    }

    protected function tearDown()
    {
        array_map('unlink', glob(__DIR__ . '/*.purging'));
        parent::tearDown();
    }

    public function testGetFilesystem()
    {
        $this->assertInstanceOf('Symfony\\Component\\Filesystem\\Filesystem', $this->store->getFilesystem());
    }

    public function testSetFilesystem()
    {
        $fs = new Filesystem();
        $this->store->setFilesystem($fs);
        $this->assertSame($fs, $this->store->getFilesystem());
    }

    public function testGetPath()
    {
        $path = $this->store->getTagPath('location-123') . DIRECTORY_SEPARATOR . 'en' . sha1('someContent');
        $this->assertStringStartsWith(__DIR__ . DIRECTORY_SEPARATOR . 'ez' . DIRECTORY_SEPARATOR . '32' . DIRECTORY_SEPARATOR . '1-' . DIRECTORY_SEPARATOR . 'noitacol', $path);
    }

    public function testGetStalePath()
    {
        $this->markTestIncomplete('@todo Stale handling removed, needs adjustments once it is re added in new form');
        // Generate the lock file to force using the stale cache dir
        $locationId = 123;
        $prefix = TagAwareStore::TAG_CACHE_DIR . DIRECTORY_SEPARATOR . $locationId;
        $prefixStale = TagAwareStore::TAG_CACHE_DIR . DIRECTORY_SEPARATOR . $locationId;
        $lockFile = $this->store->getLocationCacheLockName($locationId);
        file_put_contents($lockFile, getmypid());

        $path = $this->store->getPath($prefix . DIRECTORY_SEPARATOR . 'en' . sha1('someContent'));
        $this->assertStringStartsWith(__DIR__ . DIRECTORY_SEPARATOR . $prefixStale, $path);
        @unlink($lockFile);
    }

    public function testGetPathDeadProcess()
    {
        $this->markTestIncomplete('@todo Stale handling removed, needs adjustments once it is re added in new form');
        if (!function_exists('posix_kill')) {
            self::markTestSkipped('posix_kill() function is needed for this test');
        }

        $locationId = 123;
        $prefix = TagAwareStore::TAG_CACHE_DIR . "/$locationId";
        $lockFile = $this->store->getLocationCacheLockName($locationId);
        file_put_contents($lockFile, '99999999999999999');

        $path = $this->store->getPath("$prefix/en" . sha1('someContent'));
        $this->assertStringStartsWith(__DIR__ . "/$prefix", $path);
        $this->assertFalse(file_exists($lockFile));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFilesystemMock()
    {
        return $this->getMock('Symfony\\Component\\Filesystem\\Filesystem');
    }

    public function testPurgeByRequestSingleLocation()
    {
        $this->markTestIncomplete('@todo needs adjustments for new impl on top of tags');
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem($fs);
        $locationId = 123;
        $locationCacheDir = $this->store->getTagPath('location-' . $locationId);
        $staleCacheDir = str_replace(TagAwareStore::TAG_CACHE_DIR, TagAwareStore::TAG_CACHE_DIR, $locationCacheDir);

        $fs
            ->expects($this->any())
            ->method('exists')
            ->with($locationCacheDir)
            ->will($this->returnValue(true));
        $fs
            ->expects($this->once())
            ->method('mkdir')
            ->with($staleCacheDir);
        $fs
            ->expects($this->once())
            ->method('mirror')
            ->with($locationCacheDir, $staleCacheDir);
        $fs
            ->expects($this->once())
            ->method('remove')
            ->with($locationCacheDir);

        $request = Request::create('/', 'PURGE');
        $request->headers->set('X-Location-Id', "$locationId");
        $this->store->purgeByRequest($request);
    }

    public function testPurgeByRequestMultipleLocations()
    {
        $this->markTestIncomplete('@todo needs adjustments for new impl on top of tags');
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem($fs);
        $locationIds = array(123, 456, 789);
        $i = 0;
        foreach ($locationIds as $locationId) {
            $locationCacheDir = $this->store->getTagPath('location-' . $locationId);
            $staleCacheDir = str_replace(TagAwareStore::TAG_CACHE_DIR, TagAwareStore::TAG_CACHE_DIR, $locationCacheDir);

            $fs
                ->expects($this->at($i++))
                ->method('exists')
                ->with($locationCacheDir)
                ->will($this->returnValue(true));
            $fs
                ->expects($this->at($i++))
                ->method('mkdir')
                ->with($staleCacheDir);
            $fs
                ->expects($this->at($i++))
                ->method('mirror')
                ->with($locationCacheDir, $staleCacheDir);
            $fs
                ->expects($this->at($i++))
                ->method('remove')
                ->with($locationCacheDir);
        }

        $request = Request::create('/', 'BAN');
        $request->headers->set('X-Location-Id', '(' . implode('|', $locationIds) . ')');
        $this->store->purgeByRequest($request);
    }

    public function testPurgeAllContent()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem($fs);
        $locationCacheDir = $this->store->getTagPath();

        $fs
            ->expects($this->once())
            ->method('remove')
            ->with($locationCacheDir);

        $this->store->purgeAllContent();
    }

    public function testPurgeAllContentByRequest()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem($fs);
        $locationCacheDir = $this->store->getTagPath();

        $fs
            ->expects($this->once())
            ->method('remove')
            ->with($locationCacheDir);

        $request = Request::create('/', 'BAN');
        $request->headers->set('X-Location-Id', '.*');
        $this->store->purgeByRequest($request);
    }

    public function testPurgeAllContentByRequestBC()
    {
        $fs = $this->getFilesystemMock();
        $this->store->setFilesystem($fs);
        $locationCacheDir = $this->store->getTagPath();

        $fs
            ->expects($this->once())
            ->method('remove')
            ->with($locationCacheDir);

        $request = Request::create('/', 'PURGE');
        $request->headers->set('X-Location-Id', '*');
        $this->store->purgeByRequest($request);
    }
}
