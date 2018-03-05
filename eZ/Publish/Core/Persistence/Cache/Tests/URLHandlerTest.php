<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Tests\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\Core\Persistence\Cache\Tests\AbstractCacheHandlerTest;
use eZ\Publish\SPI\Persistence\URL\Handler as SpiURLHandler;
use eZ\Publish\SPI\Persistence\URL\URL;
use eZ\Publish\SPI\Persistence\URL\URLUpdateStruct;

class URLHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'urlHandler';
    }

    public function getHandlerClassName(): string
    {
        return SpiURLHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['find', [new URLQuery()]],
            ['findUsages', [1]],
            ['loadByUrl', ['http://google.com']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $url = new URL(['id' => 1]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['loadById', [1], 'ez-url-1', [$url]],
        ];
    }

    public function testUpdateUrlWhenAddressIsUpdated()
    {
        $urlId = 1;
        $updateStruct = new URLUpdateStruct();
        $updateStruct->url = 'http://ez.no';

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->createMock(SpiURLHandler::class);
        $this->persistenceHandlerMock
            ->expects($this->any())
            ->method('urlHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->exactly(1))
            ->method('findUsages')
            ->with($urlId)
            ->willReturn([2, 3, 5]);

        $innerHandlerMock
            ->expects($this->exactly(1))
            ->method('updateUrl')
            ->with($urlId, $updateStruct)
            ->willReturn(true);

        $this->cacheMock
            ->expects($this->at(0))
            ->method('invalidateTags')
            ->with(['url-1']);

        $this->cacheMock
            ->expects($this->at(1))
            ->method('invalidateTags')
            ->with(['content-2', 'content-3', 'content-5']);

        $handler = $this->persistenceCacheHandler->urlHandler();
        $handler->updateUrl($urlId, $updateStruct);
    }

    public function testUpdateUrlStatusIsUpdated()
    {
        $urlId = 1;
        $updateStruct = new URLUpdateStruct();

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->createMock(SpiURLHandler::class);
        $this->persistenceHandlerMock
            ->expects($this->any())
            ->method('urlHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->exactly(1))
            ->method('updateUrl')
            ->with($urlId, $updateStruct)
            ->willReturn(true);

        $this->cacheMock
            ->expects($this->at(0))
            ->method('invalidateTags')
            ->with(['url-1']);

        $handler = $this->persistenceCacheHandler->urlHandler();
        $handler->updateUrl($urlId, $updateStruct);
    }
}
