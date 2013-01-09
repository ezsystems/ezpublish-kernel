<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Cache\Tests\PersistenceHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Type as SPIType;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct as SPITypeCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct as SPITypeUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPITypeFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group as SPITypeGroup;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as SPITypeGroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as SPITypeGroupUpdateStruct;

/**
 * Test case for Persistence\Cache\ContentTypeHandler
 */
class ContentTypeHandlerTest extends HandlerTest
{
    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::createGroup
     */
    public function testCreateGroup()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'createGroup' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group\\CreateStruct' ) )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->createGroup( new SPITypeGroupCreateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'updateGroup' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group\\UpdateStruct' ) )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->updateGroup( new SPITypeGroupUpdateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'deleteGroup' )
            ->with( 55 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->deleteGroup( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadGroup
     */
    public function testLoadGroup()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadGroup' )
            ->with( 55 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->loadGroup( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadGroupByIdentifier
     */
    public function testLoadGroupByIdentifier()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadGroupByIdentifier' )
            ->with( 'media' )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->loadGroupByIdentifier( 'media' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadAllGroups' )
            ->with()
            ->will( $this->returnValue( array() ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->loadAllGroups();
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadContentTypes
     */
    public function testLoadContentTypes()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadContentTypes' )
            ->with( 55 )
            ->will( $this->returnValue( array() ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->loadContentTypes( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::load
     */
    public function testLoadDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'load' )
            ->with( 55 )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum'  )
                    )
                )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->load( 55, SPIType::STATUS_DRAFT );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::load
     */
    public function testLoadCacheIsMiss()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'contentType', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'load' )
            ->with( 55 )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->load( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::load
     */
    public function testLoadHasCache()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'contentType', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will( $this->returnValue( false ) );

        $this->persistenceFactoryMock
            ->expects( $this->never() )
            ->method( 'getContentTypeHandler' );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'set' );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->load( 55 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadByIdentifier' )
            ->with( 'forum' )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->loadByIdentifier( 'forum' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'loadByRemoteId' )
            ->with( 'cwr34ln43njntekwf' )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->loadByRemoteId( 'cwr34ln43njntekwf' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::create
     */
    public function testCreate()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'contentType', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'create' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\CreateStruct' ) )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum', 'status' => SPIType::STATUS_DEFINED  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->create( new SPITypeCreateStruct( array( 'status' => SPIType::STATUS_DEFINED ) ) );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::create
     */
    public function testCreateWithDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'create' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\CreateStruct' ) )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum'  )
                    )
                )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->create( new SPITypeCreateStruct( array( 'status' => SPIType::STATUS_DRAFT ) ) );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::update
     */
    public function testUpdate()
    {
        $cacheItemMock = $this->getMock( 'Stash\\Cache', array(), array(), '', false );
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'contentType', 55 )
            ->will( $this->returnValue( $cacheItemMock ) );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'update' )
            ->with(
                55,
                SPIType::STATUS_DEFINED,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\UpdateStruct' )
            )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum'  )
                    )
                )
            );

        $cacheItemMock
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type' ) );

        $cacheItemMock
            ->expects( $this->never() )
            ->method( 'get' );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->update( 55, SPIType::STATUS_DEFINED, new SPITypeUpdateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::update
     */
    public function testUpdateDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'update' )
            ->with(
                55,
                SPIType::STATUS_DRAFT,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\UpdateStruct' )
            )
            ->will(
                $this->returnValue(
                    new SPIType(
                        array( 'id' => 55, 'name' => 'Forum', 'identifier' => 'forum'  )
                    )
                )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->update( 55, SPIType::STATUS_DRAFT, new SPITypeUpdateStruct );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::delete
     */
    public function testDelete()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'delete' )
            ->with( 44, SPIType::STATUS_DEFINED )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->delete( 44, SPIType::STATUS_DEFINED );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::delete
     */
    public function testDeleteDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'delete' )
            ->with( 44, SPIType::STATUS_DRAFT )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->delete( 44, SPIType::STATUS_DRAFT );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::createDraft
     */
    public function testCreateDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'createDraft' )
            ->with( 14, 33 )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->createDraft( 14, 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::copy
     */
    public function testCopy()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'copy' )
            ->with( 14, 33, SPIType::STATUS_DEFINED )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->copy( 14, 33, SPIType::STATUS_DEFINED );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::link
     */
    public function testLink()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'link' )
            ->with( 22, 44, SPIType::STATUS_DEFINED )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->link( 22, 44, SPIType::STATUS_DEFINED );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::link
     */
    public function testLinkDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'link' )
            ->with( 22, 44, SPIType::STATUS_DRAFT )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->link( 22, 44, SPIType::STATUS_DRAFT );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::unlink
     */
    public function testUnlink()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'unlink' )
            ->with( 22, 44, SPIType::STATUS_DEFINED )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->unlink( 22, 44, SPIType::STATUS_DEFINED );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::unlink
     */
    public function testUnlinkDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'unlink' )
            ->with( 22, 44, SPIType::STATUS_DRAFT )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->unlink( 22, 44, SPIType::STATUS_DRAFT );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::getFieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandler ) );

        $innerHandler
            ->expects( $this->once() )
            ->method( 'getFieldDefinition' )
            ->with( 33, SPIType::STATUS_DEFINED )
            ->will( $this->returnValue( null ) );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->getFieldDefinition( 33, SPIType::STATUS_DEFINED );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'addFieldDefinition' )
            ->with(
                44,
                SPIType::STATUS_DEFINED,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition' )
            )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->addFieldDefinition( 44, SPIType::STATUS_DEFINED, new SPITypeFieldDefinition );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::addFieldDefinition
     */
    public function testAddFieldDefinitionDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'addFieldDefinition' )
            ->with(
                44,
                SPIType::STATUS_DRAFT,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition' )
            )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->addFieldDefinition( 44, SPIType::STATUS_DRAFT, new SPITypeFieldDefinition );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::removeFieldDefinition
     */
    public function testRemoveFieldDefinition()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'removeFieldDefinition' )
            ->with(
                44,
                SPIType::STATUS_DEFINED,
                33
            )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->removeFieldDefinition( 44, SPIType::STATUS_DEFINED, 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::removeFieldDefinition
     */
    public function testRemoveFieldDefinitionDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'removeFieldDefinition' )
            ->with(
                44,
                SPIType::STATUS_DRAFT,
                33
            )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->removeFieldDefinition( 44, SPIType::STATUS_DRAFT, 33 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::updateFieldDefinition
     */
    public function testUpdateFieldDefinition()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'updateFieldDefinition' )
            ->with(
                44,
                SPIType::STATUS_DEFINED,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition' )
            )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->updateFieldDefinition( 44, SPIType::STATUS_DEFINED, new SPITypeFieldDefinition );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::updateFieldDefinition
     */
    public function testUpdateFieldDefinitionDraft()
    {
        $this->cacheMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'updateFieldDefinition' )
            ->with(
                44,
                SPIType::STATUS_DRAFT,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition' )
            )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->updateFieldDefinition( 44, SPIType::STATUS_DRAFT, new SPITypeFieldDefinition );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Cache\ContentTypeHandler::publish
     */
    public function testPublish()
    {
        $this->cacheMock
            ->expects( $this->once() )
            ->method( 'clear' )
            ->with( 'contentType', 44 )
            ->will( $this->returnValue( true ) );

        $innerHandlerMock = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler' );
        $this->persistenceFactoryMock
            ->expects( $this->once() )
            ->method( 'getContentTypeHandler' )
            ->will( $this->returnValue( $innerHandlerMock ) );

        $innerHandlerMock
            ->expects( $this->once() )
            ->method(  'publish' )
            ->with( 44 )
            ->will(
                $this->returnValue( true )
            );

        $handler = $this->persistenceHandler->contentTypeHandler();
        $handler->publish( 44 );
    }
}
