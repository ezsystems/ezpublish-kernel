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
class ContentTypeHandlerTest extends AbstractInMemoryCacheHandlerTest
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
        $groupUpdate = new SPITypeGroupUpdateStruct(['id' => 3, 'identifier' => 'media']);
        $typeUpdate = new SPITypeUpdateStruct(['identifier' => 'article', 'remoteId' => '34o9tj8394t']);

        // string $method, array $arguments, array? $tags, array? $key, mixed? $returnValue
        return [
            ['createGroup', [new SPITypeGroupCreateStruct()], null, ['ez-content-type-group-list']],
            ['updateGroup', [$groupUpdate], null, ['ez-content-type-group-list', 'ez-content-type-group-3', 'ez-content-type-group-media-by-identifier']],
            ['deleteGroup', [3], ['type-group-3']],
            ['loadContentTypes', [3, 1]], // also listed for cached cases in providerForCachedLoadMethods
            ['create', [new SPITypeCreateStruct(['groupIds' => [2, 3]])], null, ['ez-content-type-list-by-group-2', 'ez-content-type-list-by-group-3']],
            ['update', [5, 0, $typeUpdate], ['type-5', 'type-map', 'content-fields-type-5']],
            ['update', [5, 1, $typeUpdate], null, ['ez-content-type-5-1']],
            ['delete', [5, 0], ['type-5', 'type-map', 'content-fields-type-5']],
            ['delete', [5, 1], null, ['ez-content-type-5-1']],
            ['createDraft', [10, 5], null, ['ez-content-type-5-1']],
            ['copy', [10, 5, 0]],
            ['copy', [10, 5, 1]],
            ['unlink', [3, 5, 0], ['type-5']],
            ['unlink', [3, 5, 1], null, ['ez-content-type-5-1']],
            ['link', [3, 5, 0], ['type-5'], ['ez-content-type-list-by-group-3']],
            ['link', [3, 5, 1], null, ['ez-content-type-5-1']],
            ['getFieldDefinition', [7, 1]],
            ['getFieldDefinition', [7, 0]],
            ['getContentCount', [5]],
            ['addFieldDefinition', [5, 0, new SPITypeFieldDefinition()], ['type-5', 'type-map', 'content-fields-type-5']],
            ['addFieldDefinition', [5, 1, new SPITypeFieldDefinition()], null, ['ez-content-type-5-1']],
            ['removeFieldDefinition', [5, 0, 7], ['type-5', 'type-map', 'content-fields-type-5']],
            ['removeFieldDefinition', [5, 1, 7], null, ['ez-content-type-5-1']],
            ['updateFieldDefinition', [5, 0, new SPITypeFieldDefinition()], ['type-5', 'type-map', 'content-fields-type-5']],
            ['updateFieldDefinition', [5, 1, new SPITypeFieldDefinition()], null, ['ez-content-type-5-1']],
            ['removeContentTypeTranslation', [5, 'eng-GB'], ['type-5', 'type-map', 'content-fields-type-5'], null, new SPIType()],
        ];
    }

    /**
     * @return array
     */
    public function providerForCachedLoadMethods(): array
    {
        $group = new SPITypeGroup(['id' => 3, 'identifier' => 'media']);
        $type = new SPIType(['id' => 5, 'identifier' => 'article', 'remoteId' => '34o9tj8394t']);

        // string $method, array $arguments, string $key, mixed? $data, bool? $multi, array? $additionalCalls
        return [
            ['loadGroup', [3], 'ez-content-type-group-3', $group],
            ['loadGroups', [[3]], 'ez-content-type-group-3', [3 => $group], true],
            ['loadGroupByIdentifier', ['content'], 'ez-content-type-group-content-by-identifier', $group],
            ['loadAllGroups', [], 'ez-content-type-group-list', [3 => $group]],
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
        call_user_func_array([$handler, $method], $arguments);
    }
}
