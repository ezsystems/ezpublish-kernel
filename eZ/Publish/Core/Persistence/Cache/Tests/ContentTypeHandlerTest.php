<?php

/**
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

        // string $method, array $arguments, array? $tagGeneratingArguments, array? $keyGeneratingArguments, array? $tags, array? $key, ?mixed $returnValue
        return [
            ['createGroup', [new SPITypeGroupCreateStruct()], null, [['content_type_group_list', [], true]], null, ['ibx-ctgl']],
            [
                'updateGroup',
                [$groupUpdate],
                null,
                [
                    ['content_type_group_list', [], true],
                    ['content_type_group', [3], true],
                    ['content_type_group_with_id_suffix', ['media'], true],
                ],
                null,
                ['ibx-ctgl', 'ibx-ctg-3', 'ibx-ctg-media-bi'],
            ],
            ['deleteGroup', [3], [['type_group', [3], false]], null, ['tg-3']],
            ['loadContentTypes', [3, 1]], // also listed for cached cases in providerForCachedLoadMethods
            ['load', [5, 1]], // also listed for cached case in providerForCachedLoadMethods
            [
                'create',
                [new SPITypeCreateStruct(['groupIds' => [2, 3]])],
                null,
                [
                    ['content_type_list_by_group', [2], true],
                    ['content_type_list_by_group', [3], true],
                ],
                null,
                ['ibx-ctlbg-2', 'ibx-ctlbg-3'],
             ],
            [
                'update',
                [5, 0, $typeUpdate],
                [
                    ['type', [5], false],
                    ['type_map', [], false],
                    ['content_fields_type', [5], false],
                ],
                null,
                ['t-5', 'tm', 'cft-5'],
            ],
            ['update', [5, 1, $typeUpdate]],
            [
                'delete',
                [5, 0],
                [
                    ['type', [5], false],
                    ['type_map', [], false],
                    ['content_fields_type', [5], false],
                ],
                null,
                ['t-5', 'tm', 'cft-5'],
            ],
            ['delete', [5, 1]],
            ['createDraft', [10, 5]],
            [
                'copy',
                [10, 5, 0],
                null,
                [
                    ['content_type_list_by_group', [1], true],
                    ['content_type_list_by_group', [2], true],
                ],
                null,
                ['ibx-ctlbg-1', 'ibx-ctlbg-2'],
                new SPIType(['groupIds' => [1, 2]]),
            ],
            ['copy', [10, 5, 1], null, [['content_type_list_by_group', [3], true]], null, ['ibx-ctlbg-3'], new SPIType(['groupIds' => [3]])],
            ['unlink', [3, 5, 0], [['type', [5], false]], null, ['t-5']],
            ['unlink', [3, 5, 1]],
            [
                'link',
                [3, 5, 0],
                [
                    ['type', [5], false],
                ],
                [
                    ['content_type_list_by_group', [3], true],
                ],
                ['t-5'],
                ['ibx-ctlbg-3'],
            ],
            ['link', [3, 5, 1]],
            ['getFieldDefinition', [7, 1]],
            ['getFieldDefinition', [7, 0]],
            ['getContentCount', [5]],
            [
                'addFieldDefinition',
                [5, 0, new SPITypeFieldDefinition()],
                [
                    ['type', [5], false],
                    ['type_map', [], false],
                    ['content_fields_type', [5], false],
                ],
                null,
                ['t-5', 'tm', 'cft-5'],
            ],
            ['addFieldDefinition', [5, 1, new SPITypeFieldDefinition()]],
            [
                'removeFieldDefinition',
                [5, 0, 7],
                [
                    ['type', [5], false],
                    ['type_map', [], false],
                    ['content_fields_type', [5], false],
                ],
                null,
                ['t-5', 'tm', 'cft-5'],
            ],
            ['removeFieldDefinition', [5, 1, 7]],
            [
                'updateFieldDefinition',
                [5, 0, new SPITypeFieldDefinition()],
                [
                    ['type', [5], false],
                    ['type_map', [], false],
                    ['content_fields_type', [5], false],
                ],
                null,
                ['t-5', 'tm', 'cft-5'],
            ],
            ['updateFieldDefinition', [5, 1, new SPITypeFieldDefinition()]],
            [
                'removeContentTypeTranslation',
                [5, 'eng-GB'],
                [
                    ['type', [5], false],
                    ['type_map', [], false],
                    ['content_fields_type', [5], false],
                ],
                null,
                ['t-5', 'tm', 'cft-5'],
                null,
                new SPIType(),
            ],
            ['deleteByUserAndStatus', [12, 0], [['type_without_value', [], false]], null, ['t']],
            ['deleteByUserAndStatus', [12, 1]],
        ];
    }

    /**
     * @return array
     */
    public function providerForCachedLoadMethodsHit(): array
    {
        $group = new SPITypeGroup(['id' => 3, 'identifier' => 'media']);
        $type = new SPIType(['id' => 5, 'identifier' => 'article', 'remoteId' => '34o9tj8394t']);

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            ['loadGroup', [3], 'ibx-ctg-3', null, null, [['content_type_group', [], true]], ['ibx-ctg'], $group],
            ['loadGroups', [[3]], 'ibx-ctg-3', null, null, [['content_type_group', [], true]], ['ibx-ctg'], [3 => $group], true],
            [
                'loadGroupByIdentifier',
                ['content'],
                'ibx-ctg-content-bi',
                null,
                null,
                [
                    ['content_type_group', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ibx-ctg', 'bi'],
                $group,
            ],
            ['loadAllGroups', [], 'ibx-ctgl', null, null, [['content_type_group_list', [], true]], ['ibx-ctgl'], [3 => $group]],
            ['loadContentTypes', [3, 0], 'ibx-ctlbg-3', null, null, [['content_type_list_by_group', [3], true]], ['ibx-ctlbg-3'], [$type]],
            ['loadContentTypeList', [[5]], 'ibx-ct-5', null, null, [['content_type', [], true]], ['ibx-ct'], [5 => $type], true],
            ['load', [5, 0], 'ibx-ct-5', null, null, [['content_type', [], true]], ['ibx-ct'], $type],
            [
                'loadByIdentifier',
                ['article'],
                'ibx-ct-article-bi',
                null,
                null,
                [
                    ['content_type', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ibx-ct', 'bi'],
                $type,
            ],
            [
                'loadByRemoteId',
                ['f34tg45gf'],
                'ibx-ct-f34tg45gf-br',
                null,
                null,
                [
                    ['content_type', [], true],
                    ['by_remote_suffix', [], false],
                ],
                ['ibx-ct', 'br'],
                $type,
            ],
            ['getSearchableFieldMap', [], 'ibx-ctfm', null, null, [['content_type_field_map', [], true]], ['ibx-ctfm'], [$type]],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $group = new SPITypeGroup(['id' => 3, 'identifier' => 'media']);
        $type = new SPIType(['id' => 5, 'identifier' => 'article', 'remoteId' => '34o9tj8394t']);

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            [
                'loadGroup',
                [3],
                'ibx-ctg-3',
                [
                    ['type_group', [3], false],
                ],
                ['tg-3'],
                [
                    ['content_type_group', [], true],
                ],
                ['ibx-ctg'],
                $group,
            ],
            [
                'loadGroups',
                [[3]],
                'ibx-ctg-3',
                [
                    ['type_group', [3], false],
                ],
                ['tg-3'],
                [
                    ['content_type_group', [], true],
                ],
                ['ibx-ctg'],
                [3 => $group],
                true,
            ],
            [
                'loadGroupByIdentifier',
                ['content'],
                'ibx-ctg-content-bi',
                [
                    ['type_group', [3], false],
                ],
                ['tg-3'],
                [
                    ['content_type_group', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ibx-ctg', 'bi'],
                $group,
            ],
            [
                'loadAllGroups',
                [],
                'ibx-ctgl',
                [
                    ['type_group', [3], false],
                ],
                ['tg-3'],
                [
                    ['content_type_group_list', [], true],
                ],
                ['ibx-ctgl'],
                [3 => $group],
            ],
            [
                'loadContentTypes',
                [3, 0],
                'ibx-ctlbg-3',
                [
                    ['type_group', [3], false],
                    ['type', [], false],
                    ['type', [5], false],
                ],
                ['tg-3', 't', 't-5'],
                [
                    ['content_type_list_by_group', [3], true],
                ],
                ['ibx-ctlbg-3'],
                    [$type],
            ],
            [
                'loadContentTypeList',
                [[5]],
                'ibx-ct-5',
                [
                    ['type', [], false],
                    ['type', [5], false],
                ],
                ['t-3', 't-5'],
                [
                    ['content_type', [], true],
                ],
                ['ibx-ct'],
                [5 => $type],
                true,
            ],
            [
                'load',
                [5, 0],
                'ibx-ct-5',
                [
                    ['type', [], false],
                    ['type', [5], false],
                ],
                ['t-3', 't-5'],
                [
                    ['content_type', [], true],
                ],
                ['ibx-ct'],
                $type,
            ],
            [
                'loadByIdentifier',
                ['article'],
                'ibx-ct-article-bi',
                [
                    ['type', [], false],
                    ['type', [5], false],
                ],
                ['t', 't-5'],
                [
                    ['content_type', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ibx-ct', 'bi'],
                $type,
            ],
            [
                'loadByRemoteId',
                ['f34tg45gf'],
                'ibx-ct-f34tg45gf-br',
                [
                    ['type', [], false],
                    ['type', [5], false],
                ],
                ['t', 't-5'],
                [
                    ['content_type', [], true],
                    ['by_remote_suffix', [], false],
                ],
                ['ibx-ct', 'br'],
                $type,
            ],
            [
                'getSearchableFieldMap',
                [],
                'ibx-ctfm',
                [
                    ['type_map', [], false],
                ],
                ['tm'],
                [
                    ['content_type_field_map', [], true],
                ],
                ['ibx-ctfm'],
                [$type],
            ],
        ];
    }

    /**
     * Test cache invalidation when publishing Content Type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::publish
     */
    public function testPublish()
    {
        $tags = ['t-5', 'tm', 'cft-5'];
        $method = 'publish';
        $arguments = [5];
        $type = new SPIType(['id' => 5, 'groupIds' => [3, 4]]);
        $cacheItem = $this->getCacheItem('ibx-ct-5', $type);

        $handlerMethodName = $this->getHandlerMethodName();

        $this->loggerMock->expects($this->once())->method('logCall');

        $innerHandler = $this->createMock($this->getHandlerClassName());
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method($handlerMethodName)
            ->willReturn($innerHandler);

        $innerHandler
            ->expects($this->once())
            ->method($method)
            ->with(...$arguments)
            ->willReturn(null);

        $this->cacheIdentifierGeneratorMock
            ->expects($this->exactly(3))
            ->method('generateTag')
            ->withConsecutive(
                ['type', [5], false],
                ['type_map', [], false],
                ['content_fields_type', [5], false]
            )
            ->willReturnOnConsecutiveCalls(
                't-5',
                'tm',
                'cft-5'
            );

        $this->cacheIdentifierGeneratorMock
            ->expects($this->exactly(3))
            ->method('generateKey')
            ->withConsecutive(
                ['content_type', [], true],
                ['content_type_list_by_group', [3], true],
                ['content_type_list_by_group', [4], true]
            )
            ->willReturnOnConsecutiveCalls(
                'ibx-ct',
                'ibx-ctlbg-3',
                'ibx-ctlbg-4'
            );

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
            ->with(['ibx-ctlbg-3', 'ibx-ctlbg-4'])
            ->willReturn(true);

        $handler = $this->persistenceCacheHandler->$handlerMethodName();
        call_user_func_array([$handler, $method], $arguments);
    }
}
