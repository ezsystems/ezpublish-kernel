<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\API\Repository\Values\URL\URLQuery;
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
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, string? $key
        return [
            ['find', [new URLQuery()]],
            ['findUsages', [1]],
            ['loadByUrl', ['http://google.com']],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $url = new URL(['id' => 1]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            ['loadById', [1], 'ez-url-1', [['url', [1], true]], ['ez-url-1'], [$url]],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $url = new URL(['id' => 1]);

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            [
                'loadById',
                [1],
                'ez-url-1',
                [
                    ['url', [1], true],
                    ['url', [1], false],
                ],
                ['ez-url-1', 'url-1'],
                [$url]
            ],
        ];
    }

    public function testUpdateUrlWhenAddressIsUpdated(): void
    {
        $urlId = 1;
        $updateStruct = new URLUpdateStruct();
        $updateStruct->url = 'http://ez.no';

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandlerMock = $this->createMock(SpiURLHandler::class);
        $this->persistenceHandlerMock
            ->method('urlHandler')
            ->willReturn($innerHandlerMock);

        $innerHandlerMock
            ->expects($this->once())
            ->method('findUsages')
            ->with($urlId)
            ->willReturn([2, 3, 5]);

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateUrl')
            ->with($urlId, $updateStruct)
            ->willReturn(true);

        $this->tagGeneratorMock
            ->expects($this->exactly(4))
            ->method('generate')
            ->withConsecutive(
                ['url', [1], false],
                ['content', [2], false],
                ['content', [3], false],
                ['content', [5], false]
            )
            ->willReturnOnConsecutiveCalls(
                'url-1',
                'c-2',
                'c-3',
                'c-5'
            );

        $this->cacheMock
            ->expects($this->at(0))
            ->method('invalidateTags')
            ->with(['url-1']);

        $this->cacheMock
            ->expects($this->at(1))
            ->method('invalidateTags')
            ->with(['c-2', 'c-3', 'c-5']);

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
            ->method('urlHandler')
            ->willReturn($innerHandlerMock);

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateUrl')
            ->with($urlId, $updateStruct)
            ->willReturn(true);

        $this->tagGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->with('url', [1], false)
            ->willReturn('url-1');

        $this->cacheMock
            ->expects($this->at(0))
            ->method('invalidateTags')
            ->with(['url-1']);

        $handler = $this->persistenceCacheHandler->urlHandler();
        $handler->updateUrl($urlId, $updateStruct);
    }
}
