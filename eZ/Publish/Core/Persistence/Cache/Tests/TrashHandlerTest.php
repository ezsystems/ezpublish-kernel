<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\Core\Persistence\Cache\ContentHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler as TrashHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Trashed;
use eZ\Publish\SPI\Persistence\Content\Relation;

/**
 * Test case for Persistence\Cache\SectionHandler.
 */
class TrashHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'trashHandler';
    }

    public function getHandlerClassName(): string
    {
        return TrashHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, string? $key
        return [
            ['loadTrashItem', [6]],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tagGeneratorResults, string $key, mixed? $data
        return [
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tagGeneratorResults, string $key, mixed? $data
        return [
        ];
    }

    public function testRecover()
    {
        $originalLocationId = 6;
        $targetLocationId = 2;
        $contentId = 42;

        $tags = [
            'c-' . $contentId,
            'lp-' . $originalLocationId,
        ];

        $handlerMethodName = $this->getHandlerMethodName();

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->createMock($this->getHandlerClassName());
        $contentHandlerMock = $this->createMock(ContentHandler::class);
        $locationHandlerMock = $this->createMock(LocationHandler::class);

        $locationHandlerMock
            ->method('load')
            ->willReturn(new Location(['id' => $originalLocationId, 'contentId' => $contentId]));

        $this->persistenceHandlerMock
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        $this->persistenceHandlerMock
            ->method('locationHandler')
            ->willReturn($locationHandlerMock);

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method($handlerMethodName)
            ->willReturn($innerHandler);

        $innerHandler
            ->expects($this->once())
            ->method('recover')
            ->with($originalLocationId, $targetLocationId)
            ->willReturn(null);

        $this->tagGeneratorMock
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['content', [$contentId], false],
                ['location_path', [$originalLocationId], false]
            )
            ->willReturnOnConsecutiveCalls(
                'c-' . $contentId,
                'lp-' . $originalLocationId
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $handler = $this->persistenceCacheHandler->$handlerMethodName();
        $handler->recover($originalLocationId, $targetLocationId);
    }

    public function testTrashSubtree()
    {
        $locationId = 6;
        $contentId = 42;

        $tags = [
            'c-' . $contentId,
            'lp-' . $locationId,
        ];

        $handlerMethodName = $this->getHandlerMethodName();

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->createMock($this->getHandlerClassName());
        $contentHandlerMock = $this->createMock(ContentHandler::class);
        $locationHandlerMock = $this->createMock(LocationHandler::class);

        $locationHandlerMock
            ->method('load')
            ->willReturn(new Location(['id' => $locationId, 'contentId' => $contentId]));

        $this->persistenceHandlerMock
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        $this->persistenceHandlerMock
            ->method('locationHandler')
            ->willReturn($locationHandlerMock);

        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method($handlerMethodName)
            ->willReturn($innerHandler);

        $innerHandler
            ->expects($this->once())
            ->method('trashSubtree')
            ->with($locationId)
            ->willReturn(null);

        $this->tagGeneratorMock
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['content', [$contentId], false],
                ['location_path', [$locationId], false]
            )
            ->willReturnOnConsecutiveCalls(...$tags);

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        $handler = $this->persistenceCacheHandler->$handlerMethodName();
        $handler->trashSubtree($locationId);
    }

    public function testDeleteTrashItem()
    {
        $trashedId = 6;
        $contentId = 42;
        $relationSourceContentId = 44;

        $handlerMethodName = $this->getHandlerMethodName();

        $innerHandler = $this->createMock($this->getHandlerClassName());

        $trashed = new Trashed(['id' => $trashedId, 'contentId' => $contentId]);
        $innerHandler
            ->expects($this->once())
            ->method('deleteTrashItem')
            ->with($trashedId)
            ->willReturn(new TrashItemDeleteResult(['trashItemId' => $trashedId, 'contentId' => $contentId]));

        $innerHandler
            ->expects($this->once())
            ->method('loadTrashItem')
            ->with($trashedId)
            ->willReturn($trashed);

        $this->persistenceHandlerMock
            ->method($handlerMethodName)
            ->willReturn($innerHandler);

        $contentHandlerMock = $this->createMock(ContentHandler::class);

        $contentHandlerMock
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with($contentId)
            ->willReturn([new Relation(['sourceContentId' => $relationSourceContentId])]);

        $this->persistenceHandlerMock
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        $tags = [
            'c-' . $contentId,
            'lp-' . $trashedId,
            'c-' . $relationSourceContentId,
        ];

        $this->tagGeneratorMock
            ->expects($this->exactly(3))
            ->method('generate')
            ->withConsecutive(
                ['content', [$relationSourceContentId], false],
                ['content', [$contentId], false],
                ['location_path', [$trashedId], false]
            )
            ->willReturnOnConsecutiveCalls(
                'c-' . $relationSourceContentId,
                'c-' . $contentId,
                'lp-' . $trashedId
            );

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        /** @var \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler $handler */
        $handler = $this->persistenceCacheHandler->$handlerMethodName();
        $handler->deleteTrashItem($trashedId);
    }

    public function testEmptyTrash()
    {
        $trashedId = 6;
        $contentId = 42;
        $relationSourceContentId = 44;

        $handlerMethodName = $this->getHandlerMethodName();

        $innerHandler = $this->createMock($this->getHandlerClassName());

        $innerHandler
            ->expects($this->exactly(2))
            ->method('findTrashItems')
            ->willReturn(new Location\Trash\TrashResult([
                'items' => [new Trashed(['id' => $trashedId, 'contentId' => $contentId])],
                // trigger the bulk loading several times to have some minimal coverage on the loop exit logic
                'totalCount' => 101,
            ]));

        $this->persistenceHandlerMock
            ->method($handlerMethodName)
            ->willReturn($innerHandler);

        $contentHandlerMock = $this->createMock(ContentHandler::class);

        $contentHandlerMock
            ->expects($this->exactly(2))
            ->method('loadReverseRelations')
            ->with($contentId)
            ->willReturn([new Relation(['sourceContentId' => $relationSourceContentId])]);

        $this->persistenceHandlerMock
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        $tagGeneratorArguments = [
            ['content', [$relationSourceContentId], false],
            ['content', [$contentId], false],
            ['location_path', [$trashedId], false],
        ];

        $tags = [
            'c-' . $relationSourceContentId,
            'c-' . $contentId,
            'lp-' . $trashedId,
        ];

        //one set of arguments and tags for each relation
        $this->tagGeneratorMock
            ->expects($this->exactly(6))
            ->method('generate')
            ->withConsecutive(...array_merge($tagGeneratorArguments, $tagGeneratorArguments))
            ->willReturnOnConsecutiveCalls(...array_merge($tags, $tags));

        $this->cacheMock
            ->expects($this->once())
            ->method('invalidateTags')
            ->with($tags);

        /** @var \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler $handler */
        $handler = $this->persistenceCacheHandler->$handlerMethodName();
        $handler->emptyTrash();
    }
}
