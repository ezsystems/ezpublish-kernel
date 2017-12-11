<?php

namespace eZ\Publish\Tests\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use eZ\Publish\Core\Persistence\Cache\URLHandler as CacheUrlHandler;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\SPI\Persistence\URL\Handler as UrlHandler;
use eZ\Publish\SPI\Persistence\URL\URL;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;
use PHPUnit\Framework\TestCase;
use Stash\Interfaces\ItemInterface;

class URLHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\URLHandler
     */
    private $urlHandler;

    protected function setUp()
    {
        parent::setUp();
        $this->cache = $this->createMock(CacheServiceDecorator::class);
        $this->persistenceHandler = $this->createMock(UrlHandler::class);

        $persistence = $this->createMock(Handler::class);
        $persistence
            ->expects($this->any())
            ->method('urlHandler')
            ->willReturn($this->persistenceHandler);

        $this->logger = $this->createMock(PersistenceLogger::class);
        $this->urlHandler = new CacheUrlHandler($this->cache, $persistence, $this->logger);
    }

    public function testUpdateUrl()
    {
        $urlUpdateStruct = new URLUpdateStruct();
        $url = $this->getUrl();

        $this->logger
            ->expects($this->once())
            ->method('logCall')
            ->with('eZ\Publish\Core\Persistence\Cache\URLHandler::updateUrl',
                [
                    'url' => $url->id,
                    'struct' => $urlUpdateStruct,
                ]);

        $this->persistenceHandler
            ->expects($this->once())
            ->method('updateUrl')
            ->with($url->id, $urlUpdateStruct)
            ->will($this->returnValue($url));

        $this->cache
            ->expects($this->at(0))
            ->method('clear')
            ->with('url', $url->id);

        $this->cache
            ->expects($this->at(1))
            ->method('clear')
            ->with('content');

        $this->assertEquals($url, $this->urlHandler->updateUrl($url->id, $urlUpdateStruct));
    }

    public function testFind()
    {
        $query = new URLQuery();

        $this->logger
            ->expects($this->once())
            ->method('logCall')
            ->with('eZ\Publish\Core\Persistence\Cache\URLHandler::find', [
                'query' => $query,
            ]);

        $this->urlHandler->find($query);
    }

    public function testLoadByIdWithCache()
    {
        $url = $this->getUrl();

        $cacheItem = $this->createMock(ItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('url', $url->id)
            ->will($this->returnValue($cacheItem));

        $cacheItem
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($url));

        $cacheItem
            ->expects($this->at(1))
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->assertEquals($url, $this->urlHandler->loadById($url->id));
    }

    public function testLoadByIdWithoutCache()
    {
        $url = $this->getUrl();

        $cacheItem = $this->createMock(ItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('url', $url->id)
            ->will($this->returnValue($cacheItem));

        $cacheItem
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($url));

        $cacheItem
            ->expects($this->at(1))
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->logger
            ->expects($this->once())
            ->method('logCall')
            ->with('eZ\Publish\Core\Persistence\Cache\URLHandler::loadById', [
                'url' => $url->id,
            ]);

        $this->persistenceHandler
            ->expects($this->once())
            ->method('loadById')
            ->with($url->id)
            ->will($this->returnValue($url));

        $cacheItem
            ->expects($this->any())
            ->method('set')
            ->with($url)
            ->will($this->returnSelf());

        $cacheItem
            ->expects($this->any())
            ->method('save')
            ->with()
            ->will($this->returnSelf());

        $this->assertEquals($url, $this->urlHandler->loadById($url->id));
    }

    public function testFindUsagesWithCache()
    {
        $url = $this->getUrl();
        $usages = [1, 2, 3];

        $cacheItem = $this->createMock(ItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('url', $url->id, 'usages')
            ->will($this->returnValue($cacheItem));

        $cacheItem
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($usages));

        $cacheItem
            ->expects($this->at(1))
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->assertEquals($usages, $this->urlHandler->findUsages($url->id));
    }

    public function testFindUsagesWithoutCache()
    {
        $url = $this->getUrl();
        $usages = [1, 2, 3];

        $cacheItem = $this->createMock(ItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with('url', $url->id, 'usages')
            ->will($this->returnValue($cacheItem));

        $cacheItem
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($usages));

        $cacheItem
            ->expects($this->at(1))
            ->method('isMiss')
            ->will($this->returnValue(true));

        $this->logger
            ->expects($this->once())
            ->method('logCall')
            ->with('eZ\Publish\Core\Persistence\Cache\URLHandler::findUsages',
                [
                    'url' => $url->id,
                ]);
        $this->persistenceHandler
            ->expects($this->once())
            ->method('findUsages')
            ->with($url->id)
            ->will($this->returnValue($usages));

        $cacheItem
            ->expects($this->any())
            ->method('set')
            ->with($usages)
            ->will($this->returnSelf());

        $cacheItem
            ->expects($this->any())
            ->method('save')
            ->with()
            ->will($this->returnSelf());

        $this->assertEquals($usages, $this->urlHandler->findUsages($url->id));
    }

    private function getUrl()
    {
        $url = new URL();
        $url->id = 12;

        return $url;
    }
}
