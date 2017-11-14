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
use Stash\Interfaces\ItemInterface;

/**
 * Test case for Persistence\Cache\ContentTypeHandler.
 */
class ContentTypeHandlerTest extends HandlerTest
{
    protected function getCacheItemMock()
    {
        return $this->createMock(ItemInterface::class);
    }

    protected function getContentTypeHandlerMock()
    {
        return $this->createMock(SPIContentTypeHandler::class);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::createGroup
     */
    public function testCreateGroup()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('contentTypeGroup', 55)
            ->will($this->returnValue($cacheItemMock));

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('createGroup')
            ->with($this->isInstanceOf(SPITypeGroupCreateStruct::class))
            ->will($this->returnValue(new SPITypeGroup(array('id' => 55))));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPITypeGroup::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->createGroup(new SPITypeGroupCreateStruct());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('contentTypeGroup', 55)
            ->will($this->returnValue($cacheItemMock));

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('updateGroup')
            ->with($this->isInstanceOf(SPITypeGroupUpdateStruct::class))
            ->will($this->returnValue(new SPITypeGroup()));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPITypeGroup::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->updateGroup(new SPITypeGroupUpdateStruct(array('id' => 55)));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->with('contentTypeGroup', 55)
            ->will($this->returnValue(true));

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('deleteGroup')
            ->with(55)
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->deleteGroup(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadGroup
     */
    public function testLoadGroupIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('contentTypeGroup', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadGroup')
            ->with(55)
            ->will($this->returnValue(new SPITypeGroup()));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPITypeGroup::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadGroup(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadGroup
     */
    public function testLoadGroupHasCache()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('contentTypeGroup', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(
                    new SPITypeGroup()
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method('contentTypeHandler');

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadGroup(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadGroupByIdentifier
     */
    public function testLoadGroupByIdentifier()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadGroupByIdentifier')
            ->with('media')
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadGroupByIdentifier('media');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadAllGroups')
            ->with()
            ->will($this->returnValue(array()));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadAllGroups();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadContentTypes
     */
    public function testLoadContentTypes()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadContentTypes')
            ->with(55)
            ->will($this->returnValue(array()));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadContentTypes(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::load
     */
    public function testLoadDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('load')
            ->with(55)
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->load(55, SPIType::STATUS_DRAFT);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::load
     */
    public function testLoadCacheIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('contentType', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('load')
            ->with(55)
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPIType::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->load(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::load
     */
    public function testLoadHasCache()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('contentType', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method('contentTypeHandler');

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->load(55);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadByIdentifier
     */
    public function testLoadByIdentifierIsMiss()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('contentType', 'identifier', 'forum')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(true));

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadByIdentifier')
            ->with('forum')
            ->will($this->returnValue(new SPIType(array('id' => 55, 'identifier' => 'forum'))));

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with(55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('contentType', 55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->never())
            ->method('get');

        $cacheItemMock2
            ->expects($this->never())
            ->method('isMiss');

        $cacheItemMock2
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPIType::class))
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('save')
            ->with();

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadByIdentifier('forum');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadByIdentifier
     */
    public function testLoadByIdentifierHasCache()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('contentType', 'identifier', 'forum')
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(55));

        $cacheItemMock
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $this->persistenceHandlerMock
            ->expects($this->never())
            ->method('contentTypeHandler');

        $cacheItemMock
            ->expects($this->never())
            ->method('set');

        // the code reuses load():
        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('contentType', 55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('isMiss')
            ->will($this->returnValue(false));

        $cacheItemMock2
            ->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $cacheItemMock2
            ->expects($this->never())
            ->method('set');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadByIdentifier('forum');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('loadByRemoteId')
            ->with('cwr34ln43njntekwf')
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->loadByRemoteId('cwr34ln43njntekwf');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::create
     */
    public function testCreate()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('contentType', 55)
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(1))
            ->method('getItem')
            ->with('contentType', 'identifier', 'forum')
            ->will($this->returnValue($cacheItemMock2));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(SPITypeCreateStruct::class))
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum', 'status' => SPIType::STATUS_DEFINED)
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPIType::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $cacheItemMock2
            ->expects($this->once())
            ->method('set')
            ->with(55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock2
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->create(new SPITypeCreateStruct(array('status' => SPIType::STATUS_DEFINED)));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::create
     */
    public function testCreateWithDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(SPITypeCreateStruct::class))
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->create(new SPITypeCreateStruct(array('status' => SPIType::STATUS_DRAFT)));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::update
     */
    public function testUpdate()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $cacheItemMock = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(0))
            ->method('getItem')
            ->with('contentType', 55)
            ->will($this->returnValue($cacheItemMock));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('contentType', 'identifier')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('searchableFieldMap')
            ->will($this->returnValue(true));

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('update')
            ->with(
                55,
                SPIType::STATUS_DEFINED,
                $this->isInstanceOf(SPITypeUpdateStruct::class)
            )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $cacheItemMock
            ->expects($this->once())
            ->method('set')
            ->with($this->isInstanceOf(SPIType::class))
            ->will($this->returnValue($cacheItemMock));

        $cacheItemMock
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock
            ->expects($this->never())
            ->method('get');

        $cacheItemMock2 = $this->getCacheItemMock();
        $this->cacheMock
            ->expects($this->at(3))
            ->method('getItem')
            ->with('contentType', 'identifier', 'forum')
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('set')
            ->with(55)
            ->will($this->returnValue($cacheItemMock2));

        $cacheItemMock2
            ->expects($this->once())
            ->method('save')
            ->with();

        $cacheItemMock2
            ->expects($this->never())
            ->method('get');

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->update(55, SPIType::STATUS_DEFINED, new SPITypeUpdateStruct());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::update
     */
    public function testUpdateDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('update')
            ->with(
                55,
                SPIType::STATUS_DRAFT,
                $this->isInstanceOf(SPITypeUpdateStruct::class)
            )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array('id' => 55, 'name' => 'Forum', 'identifier' => 'forum')
                    )
                )
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->update(55, SPIType::STATUS_DRAFT, new SPITypeUpdateStruct());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::delete
     */
    public function testDelete()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('contentType', 'identifier')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('delete')
            ->with(44, SPIType::STATUS_DEFINED)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->delete(44, SPIType::STATUS_DEFINED);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::delete
     */
    public function testDeleteDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('delete')
            ->with(44, SPIType::STATUS_DRAFT)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->delete(44, SPIType::STATUS_DRAFT);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::createDraft
     */
    public function testCreateDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('createDraft')
            ->with(14, 33)
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->createDraft(14, 33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::copy
     */
    public function testCopy()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('copy')
            ->with(14, 33, SPIType::STATUS_DEFINED)
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->copy(14, 33, SPIType::STATUS_DEFINED);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::link
     */
    public function testLink()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('link')
            ->with(22, 44, SPIType::STATUS_DEFINED)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->link(22, 44, SPIType::STATUS_DEFINED);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::link
     */
    public function testLinkDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('link')
            ->with(22, 44, SPIType::STATUS_DRAFT)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->link(22, 44, SPIType::STATUS_DRAFT);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::unlink
     */
    public function testUnlink()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('unlink')
            ->with(22, 44, SPIType::STATUS_DEFINED)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->unlink(22, 44, SPIType::STATUS_DEFINED);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::unlink
     */
    public function testUnlinkDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('unlink')
            ->with(22, 44, SPIType::STATUS_DRAFT)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->unlink(22, 44, SPIType::STATUS_DRAFT);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::getFieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('getFieldDefinition')
            ->with(33, SPIType::STATUS_DEFINED)
            ->will($this->returnValue(null));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->getFieldDefinition(33, SPIType::STATUS_DEFINED);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('searchableFieldMap')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('addFieldDefinition')
            ->with(
                44,
                SPIType::STATUS_DEFINED,
                $this->isInstanceOf(SPITypeFieldDefinition::class)
            )
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->addFieldDefinition(44, SPIType::STATUS_DEFINED, new SPITypeFieldDefinition());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::addFieldDefinition
     */
    public function testAddFieldDefinitionDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('addFieldDefinition')
            ->with(
                44,
                SPIType::STATUS_DRAFT,
                $this->isInstanceOf(SPITypeFieldDefinition::class)
            )
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->addFieldDefinition(44, SPIType::STATUS_DRAFT, new SPITypeFieldDefinition());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::removeFieldDefinition
     */
    public function testRemoveFieldDefinition()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('searchableFieldMap')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('removeFieldDefinition')
            ->with(
                44,
                SPIType::STATUS_DEFINED,
                33
            )
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->removeFieldDefinition(44, SPIType::STATUS_DEFINED, 33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::removeFieldDefinition
     */
    public function testRemoveFieldDefinitionDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('removeFieldDefinition')
            ->with(
                44,
                SPIType::STATUS_DRAFT,
                33
            )
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->removeFieldDefinition(44, SPIType::STATUS_DRAFT, 33);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::updateFieldDefinition
     */
    public function testUpdateFieldDefinition()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('searchableFieldMap')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateFieldDefinition')
            ->with(
                44,
                SPIType::STATUS_DEFINED,
                $this->isInstanceOf(SPITypeFieldDefinition::class)
            )
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->updateFieldDefinition(44, SPIType::STATUS_DEFINED, new SPITypeFieldDefinition());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::updateFieldDefinition
     */
    public function testUpdateFieldDefinitionDraft()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('updateFieldDefinition')
            ->with(
                44,
                SPIType::STATUS_DRAFT,
                $this->isInstanceOf(SPITypeFieldDefinition::class)
            )
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->updateFieldDefinition(44, SPIType::STATUS_DRAFT, new SPITypeFieldDefinition());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::publish
     */
    public function testPublish()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->at(0))
            ->method('clear')
            ->with('contentType', 44)
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(1))
            ->method('clear')
            ->with('contentType', 'identifier')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(2))
            ->method('clear')
            ->with('searchableFieldMap')
            ->will($this->returnValue(true));

        $this->cacheMock
            ->expects($this->at(3))
            ->method('clear')
            ->with('content')
            ->will($this->returnValue(true));

        $innerHandlerMock = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandlerMock));

        $innerHandlerMock
            ->expects($this->once())
            ->method('publish')
            ->with(44)
            ->will(
                $this->returnValue(true)
            );

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->publish(44);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::getContentCount
     */
    public function testGetContentCount()
    {
        $this->loggerMock->expects($this->once())->method('logCall');
        $this->cacheMock
            ->expects($this->never())
            ->method($this->anything());

        $innerHandler = $this->getContentTypeHandlerMock();
        $this->persistenceHandlerMock
            ->expects($this->once())
            ->method('contentTypeHandler')
            ->will($this->returnValue($innerHandler));

        $innerHandler
            ->expects($this->once())
            ->method('getContentCount')
            ->with(1)
            ->will($this->returnValue(18));

        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $handler->getContentCount(1);
    }
}
