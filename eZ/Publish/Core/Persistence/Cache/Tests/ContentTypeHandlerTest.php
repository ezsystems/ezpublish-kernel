<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Type as SPIType;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as SPITypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct as SPITypeUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPITypeFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group as SPITypeGroup;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as SPITypeGroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as SPITypeGroupUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as SPIContentTypeHandler;

/**
 * Test case for Persistence\Cache\ContentTypeHandler.
 */
class ContentTypeHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'contentTypeHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIContentTypeHandler::class;
    }

    /**
     * @return array
     */
    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key, mixed? $returnValue
        return [
            ['createGroup', [new SPITypeGroupCreateStruct()]],
            ['updateGroup', [new SPITypeGroupUpdateStruct(['id' => 3])], ['type-group-3']],
            ['deleteGroup', [3], ['type-group-3']],
            ['loadAllGroups', []],
            ['loadContentTypes', [3, 1]], // also listed for cached cases in providerForCachedLoadMethods
            ['create', [new SPITypeCreateStruct()]],
            ['update', [5, 0, new SPITypeUpdateStruct()], ['type-5', 'type-map', 'content-fields-type-5']],
            ['update', [5, 1, new SPITypeUpdateStruct()], [], 'ez-content-type-5-1'],
            ['delete', [5, 0], ['type-5', 'type-map', 'content-fields-type-5']],
            ['delete', [5, 1], null, 'ez-content-type-5-1'],
            ['createDraft', [10, 5], null, 'ez-content-type-5-1'],
            ['copy', [10, 5, 0]],
            ['copy', [10, 5, 1]],
            ['unlink', [3, 5, 0], ['type-5']],
            ['unlink', [3, 5, 1], [], 'ez-content-type-5-1'],
            ['link', [3, 5, 0], ['type-5'], 'ez-content-type-list-by-group-3'],
            ['link', [3, 5, 1], null, 'ez-content-type-5-1'],
            ['getFieldDefinition', [7, 1]],
            ['getFieldDefinition', [7, 0]],
            ['getContentCount', [5]],
            ['addFieldDefinition', [5, 0, new SPITypeFieldDefinition()], ['type-5', 'type-map', 'content-fields-type-5']],
            ['addFieldDefinition', [5, 1, new SPITypeFieldDefinition()], [], 'ez-content-type-5-1'],
            ['removeFieldDefinition', [5, 0, 7], ['type-5', 'type-map', 'content-fields-type-5']],
            ['removeFieldDefinition', [5, 1, 7], null, 'ez-content-type-5-1'],
            ['updateFieldDefinition', [5, 0, new SPITypeFieldDefinition()], ['type-5', 'type-map', 'content-fields-type-5']],
            ['updateFieldDefinition', [5, 1, new SPITypeFieldDefinition()], [], 'ez-content-type-5-1'],
        ];
    }

    /**
     * @return array
     */
    public function providerForCachedLoadMethods(): array
    {
        $group = new SPITypeGroup(['id' => 3]);
        $type = new SPIType(['id' => 5]);

        // string $method, array $arguments, string $key, mixed? $data, bool? $multi, array? $additionalCalls
        return [
            ['loadGroup', [3], 'ez-content-type-group-3', $group],
            ['loadGroups', [[3]], 'ez-content-type-group-3', [3 => $group], true],
            ['loadGroupByIdentifier', ['content'], 'ez-content-type-group-content-by-identifier', $group],
            ['loadContentTypes', [3, 0], 'ez-content-type-list-by-group-3', [$type]],
            ['loadContentTypeList', [[5]], 'ez-content-type-5-0', [5 => $type], true],
            ['load', [5, 0], 'ez-content-type-5-0', $type],
            ['loadByIdentifier', ['article'], 'ez-content-type-article-by-identifier', $type],
            ['loadByRemoteId', ['f34tg45gf'], 'ez-content-type-f34tg45gf-by-remote', $type],
            ['getSearchableFieldMap', [], 'ez-content-type-field-map', [$type]],
        ];
    }

    /**
     * Test cache invalidation when publishing Content Type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::publish
     */
    public function testPublish()
    {
        $tags = ['type-5', 'type-map', 'content-fields-type-5'];
        $method = 'publish';
        $arguments = [5];
        $type = new SPIType(['id' => 5, 'groupIds' => [3, 4]]);
        $cacheItem = $this->getCacheItem('ez-content-type-5-0', $type);

        $handlerMethodName = $this->getHandlerMethodName();

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->createMock($this->getHandlerClassName());
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method($handlerMethodName)
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method($method)
            ->with(...$arguments)
            ->will($this->returnValue(null));

        $this->cacheMock
            ->expects(!empty($tags) ? $this->once() : $this->never())
            ->method('invalidateTags')
            ->with($tags);

        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with($cacheItem->getKey())
            ->willReturn($cacheItem);

        $this->cacheMock
            ->expects($this->once())
            ->method('deleteItems')
            ->with(['ez-content-type-list-by-group-3', 'ez-content-type-list-by-group-4'])
            ->willReturn(true);

        $handler = $this->persistenceCacheHandler->$handlerMethodName();
        call_user_func_array(array($handler, $method), $arguments);
    }
}
