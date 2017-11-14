<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\UrlAlias;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as SPIUrlAliasHandler;
use Stash\Interfaces\ItemInterface;

/**
 * Test case for Persistence\Cache\UrlAliasHandler.
 */
class UrlAliasHandlerTest extends HandlerTest
{
    protected function getCacheItemMock()
    {
        return $this->createMock(ItemInterface::class);
    }

    protected function getSPIUrlAliasHandlerMock()
    {
        return $this->createMock(SPIUrlAliasHandler::class);
    }

    /**
     * @return array
     */
    public function providerForUnCachedMethods()
    {
        return array(
            //array( 'publishUrlAliasForLocation', array( 44, 2, 'name', 'eng-GB', true ) ),
            //array( 'createCustomUrlAlias', array( 44, '/path', true, 'eng-GB', true ) ),
            //array( 'createGlobalUrlAlias', array( '/old', '/path', true, 'eng-GB', true ) ),
            array('listGlobalURLAliases', array('eng-GB', 10, 5)),
            //array( 'listURLAliasesForLocation', array( 44, true ) ),
            //array( 'removeURLAliases', array( array( 1, 2 ) ) ),
            //array( 'lookup', array( '/url' ) ),
            //array( 'loadUrlAlias', array( 88 ) ),
            //array( 'locationMoved', array( 44, 2, 45 ) ),
            //array( 'locationCopied', array( 44, 2, 45 ) ),
            //array( 'locationDeleted', array( 44 ) ),
        );
    }

    /**
     * @dataProvider providerForUnCachedMethods
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler
     */
    public function testUnCachedMethods($method, array $arguments)
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $expects = $innerHandler
            ->expects($this->once())
            ->method($method);

        if (isset($arguments[4])) {
            $expects->with($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
        } elseif (isset($arguments[3])) {
            $expects->with($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
        } elseif (isset($arguments[2])) {
            $expects->with($arguments[0], $arguments[1], $arguments[2]);
        } elseif (isset($arguments[1])) {
            $expects->with($arguments[0], $arguments[1]);
        } elseif (isset($arguments[0])) {
            $expects->with($arguments[0]);
        }

        $expects->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        call_user_func_array(array($handler, $method), $arguments);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasForLocationWithoutCachedLocation()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $cacheItem = $this->getCacheItemMock();

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('publishUrlAliasForLocation')
            ->with(44, 2, 'name', 'eng-GB', true)
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('urlAlias')
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->publishUrlAliasForLocation(44, 2, 'name', 'eng-GB', true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasForLocationWithCachedLocation()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $cacheItem = $this->getCacheItemMock();

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('publishUrlAliasForLocation')
            ->with(44, 2, 'name', 'eng-GB', true)
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->publishUrlAliasForLocation(44, 2, 'name', 'eng-GB', true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::createCustomUrlAlias
     */
    public function testCreateCustomUrlAliasHasCache()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $urlAlias = new UrlAlias(array('id' => 55, 'destination' => 44));
        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('createCustomUrlAlias')
            ->with(44, '/path', true, 'eng-GB', true)
            ->will($this->returnValue($urlAlias));

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->never())
            ->method('get');
        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($urlAlias)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 'location', 44, 'custom')
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array(42)));

        $cacheItemMock2
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock2
            ->expects($this->once())
            ->method('set')
            ->with(array(42, 55))
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->createCustomUrlAlias(44, '/path', true, 'eng-GB', true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::createCustomUrlAlias
     */
    public function testCreateCustomUrlAliasIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $urlAlias = new UrlAlias(array('id' => 55, 'destination' => 44));
        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('createCustomUrlAlias')
            ->with(44, '/path', true, 'eng-GB', true)
            ->will($this->returnValue($urlAlias));

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->never())
            ->method('get');
        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($urlAlias)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 'location', 44, 'custom')
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock2
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $cacheItemMock2
            ->expects($this->once())
            ->method('set')
            ->with(array(55))
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->createCustomUrlAlias(44, '/path', true, 'eng-GB', true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::createGlobalUrlAlias
     */
    public function testCreateGlobalUrlAlias()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('createGlobalUrlAlias')
            ->with('/old', '/path', true, 'eng-GB', true)
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(UrlAlias::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->createGlobalUrlAlias('/old', '/path', true, 'eng-GB', true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('urlAlias', 'location', 44)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('listURLAliasesForLocation')
            ->with(44, false)
            ->will(
                $this->returnValue(
                    array(
                        new UrlAlias(array('id' => 55)),
                        new UrlAlias(array('id' => 58)),
                        new UrlAlias(array('id' => 91)),
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with(array(55, 58, 91))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation(44, false);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationCustomIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('urlAlias', 'location', '44', 'custom')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('listURLAliasesForLocation')
            ->with(44, true)
            ->will(
                $this->returnValue(
                    array(
                        new UrlAlias(array('id' => 55)),
                        new UrlAlias(array('id' => 58)),
                        new UrlAlias(array('id' => 91)),
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with(array(55, 58, 91))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation(44, true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 'location', 44)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array(55, 58, 91)));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        // inline calls to loadUrlAlias() using the cache
        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('getItem')
            ->with('urlAlias', 58)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 58))));

        $this->cacheMock
            ->expects($this->at(3))
            ->method('getItem')
            ->with('urlAlias', 91)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->at(2))
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 91))));

        $cacheItemMock2
            ->expects($this->exactly(3))
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock2
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation(44, false);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocationCustomHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 'location', '44', 'custom')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array(55, 58, 91)));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('getItem')
            ->with('urlAlias', 58)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 58))));

        $this->cacheMock
            ->expects($this->at(3))
            ->method('getItem')
            ->with('urlAlias', 91)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->at(2))
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 91))));

        $cacheItemMock2
            ->expects($this->exactly(3))
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock2
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->listURLAliasesForLocation(44, true);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::removeURLAliases
     */
    public function testRemoveURLAliases()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('removeURLAliases');

        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('urlAlias', 'url');

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('urlAlias', 21);

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('urlAlias', 32);

        $this->cacheMock
            ->expects($this->at(3))
            ->method('clear')
            ->with('urlAlias', 'location', 44);

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->removeURLAliases(
            array(
                new UrlAlias(array('id' => 21)),
                new UrlAlias(array('id' => 32, 'type' => UrlAlias::LOCATION, 'destination' => 44)),
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::lookup
     */
    public function testLookupIsMissActive()
    {
        $urlAlias = new UrlAlias(
            [
                'id' => 55,
                'isHistory' => false,
            ]
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $missedUrlAliasIdCacheItem = $this->getCacheItemMock();
        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('set')
            ->with(55)
            ->will($this->returnValue($missedUrlAliasIdCacheItem));

        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('save')
            ->with();

        $newUrlAliasCacheItem = $this->getCacheItemMock();
        $newUrlAliasCacheItem
            ->expects($this->once())
            ->method('set')
            ->with($urlAlias)
            ->will($this->returnValue($newUrlAliasCacheItem));

        $newUrlAliasCacheItem
            ->expects($this->once())
            ->method('save')
            ->with();

        $historyUrlAliasCacheItem = $this->getCacheItemMock();
        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 'url', '/url')
            ->will($this->returnValue($missedUrlAliasIdCacheItem));
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 'url', 'history', '/url')
            ->will($this->returnValue($historyUrlAliasCacheItem));
        $this->cacheMock
            ->expects($this->at(2))
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($newUrlAliasCacheItem));

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('lookup')
            ->with('/url')
            ->will($this->returnValue($urlAlias));

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->lookup('/url');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::lookup
     */
    public function testLookupIsMissHistory()
    {
        $urlAlias = new UrlAlias(
            [
                'id' => 55,
                'isHistory' => true,
            ]
        );

        $this->loggerMock->expects($this->once())->method('logCall');

        $missedUrlAliasIdCacheItem = $this->getCacheItemMock();
        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $historyUrlAliasCacheItem = $this->getCacheItemMock();
        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('set')
            ->with($urlAlias)
            ->will($this->returnValue($historyUrlAliasCacheItem));
        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('save')
            ->with();

        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 'url', '/url')
            ->will($this->returnValue($missedUrlAliasIdCacheItem));
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 'url', 'history', '/url')
            ->will($this->returnValue($historyUrlAliasCacheItem));

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('lookup')
            ->with('/url')
            ->will($this->returnValue($urlAlias));

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->lookup('/url');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::lookup
     */
    public function testLookupHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 'url', '/url')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(55));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $cacheItemMock2
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock2
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->lookup('/url');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::lookup
     */
    public function testLookupHasHistoryCache()
    {
        $urlAlias = new UrlAlias(array('id' => 55));

        $this->loggerMock
            ->expects($this->never())
            ->method('logCall');

        $missedUrlAliasIdCacheItem = $this->getCacheItemMock();
        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $missedUrlAliasIdCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $historyUrlAliasCacheItem = $this->getCacheItemMock();
        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($urlAlias));

        $historyUrlAliasCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('urlAlias', 'url', '/url')
            ->will($this->returnValue($missedUrlAliasIdCacheItem));
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('urlAlias', 'url', 'history', '/url')
            ->will($this->returnValue($historyUrlAliasCacheItem));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method('urlAliasHandler');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->lookup('/url');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::loadUrlAlias
     */
    public function testLoadUrlAliasIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadUrlAlias')
            ->with(55)
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(UrlAlias::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->loadUrlAlias(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::loadUrlAlias
     */
    public function testLoadUrlAliasHasCache()
    {
        $this->loggerMock->expects($this->never())->method('logCall');

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('urlAlias', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(new UrlAlias(array('id' => 55))));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->loadUrlAlias(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::locationMoved
     */
    public function testLocationMoved()
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('locationMoved')
            ->with(44, 2, 45);

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('urlAlias')
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->locationMoved(44, 2, 45);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::locationDeleted
     */
    public function testLocationDeletedWithoutCachedLocation()
    {
        $locationNotCached = $this->getCacheItemMock();
        $locationNotCached
            ->expects($this->once())
            ->method('isMiss')
            ->willReturn(true);
        $locationNotCached
            ->expects($this->never())
            ->method('clear');

        $this->prepareDeleteMocks($locationNotCached);

        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('urlAlias');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->locationDeleted(44);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\UrlAliasHandler::locationDeleted
     */
    public function testLocationDeletedWithCachedLocation()
    {
        $locationCacheItem = $this->getCacheItemMock();
        $locationCacheItem
            ->expects($this->once())
            ->method('isMiss')
            ->willReturn(false);
        $locationCacheItem
            ->expects($this->once())
            ->method('get')
            ->willReturn(['44'])
        ;
        $locationCacheItem
            ->expects($this->once())
            ->method('clear')
            ->will($this->returnValue(null));

        $this->prepareDeleteMocks($locationCacheItem);

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('urlAlias', 44);
        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('urlAlias', 'url');

        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $handler->locationDeleted(44);
    }

    /**
     * @param $locationCacheMissed
     */
    protected function prepareDeleteMocks($locationCacheMissed)
    {
        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->getSPIUrlAliasHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('urlAliasHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler->expects($this->once())->method('locationDeleted')->with(44);

        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->willReturn($locationCacheMissed);
    }
}
