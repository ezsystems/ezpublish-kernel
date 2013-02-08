<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * Test case for SectionHandler using in memory storage.
 */
class ContentTypeHandlerTest extends HandlerTest
{
    /**
     * Test create group function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::createGroup
     */
    public function testCreateGroup()
    {
        $group = $this->persistenceHandler->ContentTypeHandler()->createGroup( $this->getGroupCreateStruct() );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group', $group );
        $this->assertEquals( array( 'eng-GB' => 'Media' ), $group->name );
    }

    /**
     * Test update group function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $struct = new GroupUpdateStruct();
        $struct->id = 1;
        $struct->modified = time();
        $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Content2' );
        $struct->description = array( 'eng-GB' => 'TestTest' );
        $struct->identifier = 'content2';
        $this->persistenceHandler->ContentTypeHandler()->updateGroup( $struct );
        $group = $this->persistenceHandler->ContentTypeHandler()->loadGroup( 1 );
        $this->assertEquals( 1, $group->id );
        $this->assertEquals( array( 'eng-GB' => 'Content2' ), $group->name );
    }

    /**
     * Test delete group function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $group = $handler->createGroup( $this->getGroupCreateStruct() );
        $handler->deleteGroup( $group->id );

        try
        {
            $handler->loadGroup( $group->id );
            $this->fail( "Group not deleted correctly" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test delete group function where group is assigned to type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::deleteGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteGroupBadState()
    {
        $this->persistenceHandler->ContentTypeHandler()->deleteGroup( 1 );
    }

    /**
     * Test load group function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadGroup
     */
    public function testLoadGroup()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->loadGroup( 1 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $obj->name );
    }

    /**
     * Test load group by identifier function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadGroupByIdentifier
     */
    public function testLoadGroupByIdentifier()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->loadGroupByIdentifier( 'Content' );// data is in sync with legacy
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $obj->name );
    }

    /**
     * Test load all groups function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $list = $this->persistenceHandler->ContentTypeHandler()->loadAllGroups();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Group', $list[0] );
        $this->assertEquals( 1, $list[0]->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $list[0]->name );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadContentTypes
     */
    public function testLoadByGroup()
    {
        $list = $this->persistenceHandler->ContentTypeHandler()->loadContentTypes( 1, 0 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $list[0] );
        $this->assertEquals( 1, $list[0]->id );
        $this->assertEquals( 'folder', $list[0]->identifier );

        $list = $this->persistenceHandler->ContentTypeHandler()->loadContentTypes( 22, 0 );
        $this->assertEquals( array(), $list );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::load
     */
    public function testLoad()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->load( 1, 0 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'folder', $obj->identifier );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::load
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadUnExistingTypeId()
    {
        $this->persistenceHandler->ContentTypeHandler()->load( 22, 0 );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::load
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadUnExistingStatus()
    {
        $this->persistenceHandler->ContentTypeHandler()->load( 1, 1 );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->loadByIdentifier( 'folder' );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'folder', $obj->identifier );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadByIdentifier
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadByIdentifierUnExistingType()
    {
        // eZ Publish does not now about this unless you teach it
        $this->persistenceHandler->ContentTypeHandler()->loadByIdentifier( 'kamasutra' );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadByRemoteId
     */
    public function testLoadByRemoteId()
    {
        $obj = $this->persistenceHandler->ContentTypeHandler()->loadByRemoteId( 'a3d405b81be900468eb153d774f4f0d2' );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'folder', $obj->identifier );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test load function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::loadByRemoteId
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadByRemoteIdUnExistingType()
    {
        $this->persistenceHandler->ContentTypeHandler()->loadByRemoteId( 'l33t' );
    }

    /**
     * Test create function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::create
     */
    public function testCreate()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $obj = $handler->create( $this->getTypeCreateStruct() );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $this->assertEquals( array(), $obj->fieldDefinitions );
    }

    /**
     * Test create function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::create
     */
    public function testCreateWithFieldDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $struct = $this->getTypeCreateStruct();
        $struct->fieldDefinitions[] = $field = $this->getTypeFieldDefinition();

        $obj = $handler->create( $struct );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $field->id = $obj->fieldDefinitions[0]->id;
        $this->assertEquals( array( $field ), $obj->fieldDefinitions );
    }

    /**
     * Test update function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::update
     */
    public function testUpdate()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->update( 1, 0, $this->getTypeUpdateStruct() );
        $obj = $handler->load( 1, 0 );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $this->assertEquals( 10, $obj->modifierId );
    }

    /**
     * Test delete function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::delete
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testDelete()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $obj = $handler->create( $this->getTypeCreateStruct() );
        $handler->delete( $obj->id, 0 );
        $this->assertNull( $handler->load( $obj->id, 0 ) );
    }

    /**
     * Test delete function whit existing content assigned to type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteBadState()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->delete( 1, 0 );
        $this->assertNull( $handler->load( 1, 0 ) );
    }

    /**
     * Test delete function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteNotFound()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->delete( 9999, 0 );
    }

    /**
     * Test delete function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteNotFoundStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->delete( 1, 2 );
    }

    /**
     * Test createDraft function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::createDraft
     */
    public function testCreateDraft()
    {
        $userId = 10;
        $time = time();
        $obj = $this->persistenceHandler->ContentTypeHandler()->createDraft( $userId, 1 );
        $original = $this->persistenceHandler->ContentTypeHandler()->load( 1, Type::STATUS_DEFINED );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertEquals( $original->creatorId, $obj->creatorId );
        $this->assertEquals( $original->created, $obj->created );//ehm
        $this->assertEquals( $userId, $obj->modifierId );
        $this->assertGreaterThanOrEqual( $time, $obj->modified );//ehm
        $this->assertEquals( Type::STATUS_DRAFT, $obj->status );
        $this->assertEquals( 3, count( $obj->fieldDefinitions ) );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test createDraft function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::createDraft
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCreateDraftNonExistingTypeId()
    {
        $this->persistenceHandler->ContentTypeHandler()->createDraft( 10, 999 );
    }

    /**
     * Test createDraft function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::createDraft
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCreateDraftNonExistingDefinedType()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $obj = $handler->create( $this->getTypeCreateStruct( Type::STATUS_DRAFT ) );
        $obj2 = $handler->createDraft( 10, $obj->id );
    }

    /**
     * Test copy function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::copy
     */
    public function testCopy()
    {
        $userId = 10;
        $time = time();
        $obj = $this->persistenceHandler->ContentTypeHandler()->copy( $userId, 1, Type::STATUS_DEFINED );
        $original = $this->persistenceHandler->ContentTypeHandler()->load( 1, Type::STATUS_DEFINED );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type', $obj );
        $this->assertStringStartsWith( 'folder_', $obj->identifier );
        $this->assertEquals( $userId, $obj->creatorId );
        $this->assertEquals( $userId, $obj->modifierId );
        $this->assertGreaterThanOrEqual( $time, $obj->created );
        $this->assertGreaterThanOrEqual( $time, $obj->modified );
        $this->assertEquals( Type::STATUS_DEFINED, $obj->status );
        $this->assertGreaterThan( $original->created, $obj->created );
        $this->assertEquals( 3, count( $obj->fieldDefinitions ) );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );
    }

    /**
     * Test copy function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::copy
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCopyNonExistingTypeId()
    {
        $this->persistenceHandler->ContentTypeHandler()->copy( 10, 22, 0 );
    }

    /**
     * Test copy function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::copy
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCopyNonExistingStatus()
    {
        $this->persistenceHandler->ContentTypeHandler()->copy( 10, 1, 1 );
    }

    /**
     * Test link function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::link
     */
    public function testLink()
    {
        $group = $this->getGroupCreateStruct();
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $vo = $handler->createGroup( $group );
        $handler->link( $vo->id, 1, 0 );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( array( 1, $vo->id ), $type->groupIds );
    }

    /**
     * Test link function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::link
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLinkMissingGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->link( 64, 1, 0 );
    }

    /**
     * Test link function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::link
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLinkMissingType()
    {
        $this->persistenceHandler->contentTypeHandler()->link( 1, 64, 0 );
    }

    /**
     * Test link function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::link
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testLinkExistingGroupLink()
    {
        $this->persistenceHandler->contentTypeHandler()->link( 1, 1, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::unlink
     */
    public function testUnLink()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $group = $this->getGroupCreateStruct();
        $vo = $handler->createGroup( $group );
        $handler->link( $vo->id, 1, 0 );
        $handler->unlink( 1, 1, 0 );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( array( $vo->id ), $type->groupIds );
    }

    /**
     * Test unlink function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::unlink
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testUnLinkMissingGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 64, 1, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::unlink
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testUnLinkMissingType()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 1, 64, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::unlink
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testUnLinkNotInGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 2, 1, 0 );
    }

    /**
     * Test unlink function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::unlink
     * @expectedException \eZ\Publish\Core\Base\Exceptions\BadStateException
     */
    public function testUnLinkLastGroup()
    {
        $this->persistenceHandler->contentTypeHandler()->unlink( 1, 1, 0 );
    }

    /**
     * Test getFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::getFieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();

        $fieldDefinition = $handler->getFieldDefinition( 1, 0 );

        $this->assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition", $fieldDefinition );
        $this->assertEquals( "name", $fieldDefinition->identifier );
    }

    /**
     * Test addFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $field = $this->getTypeFieldDefinition();
        $vo = $handler->addFieldDefinition( 1, 0, $field );
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\FieldDefinition', $vo );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( 4, count( $type->fieldDefinitions ) );
    }

    /**
     * Test addFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::addFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testAddFieldDefinitionInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $field = $this->getTypeFieldDefinition();
        $handler->addFieldDefinition( 22, 0, $field );
    }

    /**
     * Test addFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::addFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testAddFieldDefinitionInvalidStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $field = $this->getTypeFieldDefinition();
        $handler->addFieldDefinition( 1, 1, $field );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::removeFieldDefinition
     */
    public function testRemoveFieldDefinitionDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 1, 0, 1 );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( 2, count( $type->fieldDefinitions ) );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::removeFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testRemoveFieldDefinitionInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 22, 0, 1 );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::removeFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testRemoveFieldDefinitionInvalidStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 1, 1, 1 );
    }

    /**
     * Test removeFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::removeFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testRemoveFieldDefinitionInvalidFieldId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->removeFieldDefinition( 1, 0, 22 );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::updateFieldDefinition
     */
    public function testUpdateFieldDefinitionDefinition()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 1, 0, $fieldDefinition );
        $type = $handler->load( 1, 0 );
        $this->assertEquals( 3, count( $type->fieldDefinitions ) );
        $this->assertEquals( array( 'eng-GB' => 'Name', 'nor-NB' => 'Navn' ), $type->fieldDefinitions[0]->name );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::updateFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testUpdateFieldDefinitionDefinitionInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 22, 0, $fieldDefinition );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::updateFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testUpdateFieldDefinitionDefinitionInvalidStatus()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 1, 1, $fieldDefinition );
    }

    /**
     * Test updateFieldDefinition function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::updateFieldDefinition
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testUpdateFieldDefinitionDefinitionInvalidFieldDefinitionId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->load( 1, 0 );
        $fieldDefinition = $type->fieldDefinitions[0];
        $fieldDefinition->id = 22;
        $fieldDefinition->name = $fieldDefinition->name + array( 'nor-NB' => 'Navn' );
        $handler->updateFieldDefinition( 1, 0, $fieldDefinition );
    }

    /**
     * Test publish function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::publish
     */
    public function testPublish()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $type = $handler->copy( 10, 1, Type::STATUS_DEFINED );
        try
        {
            $handler->load( $type->id, Type::STATUS_DRAFT );
            $this->fail( "Draft of Type still exists after publish()" );
        }
        catch ( \Exception $e )
        {
        }
        $type = $handler->load( $type->id, Type::STATUS_DEFINED );

        $this->assertEquals( 10, $type->creatorId );
        $this->assertEquals( 10, $type->modifierId );
        $this->assertEquals( array( 'eng-GB' => 'Folder' ), $type->name );
        $this->assertEquals( 3, count( $type->fieldDefinitions ) );
        $this->assertEquals( array( 'eng-GB' => 'Name' ), $type->fieldDefinitions[0]->name );

        $org = $handler->load( 1, Type::STATUS_DEFINED );
        $this->assertNotEquals( $org->id, $type->id );
        $this->assertNotEquals( $org->remoteId, $type->remoteId );
        $this->assertNotEquals( $org->identifier, $type->identifier );
        $this->assertGreaterThan( $org->created, $type->created );
        $this->assertGreaterThan( $org->modified, $type->modified );
    }

    /**
     * Test publish function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::publish
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testPublishInvalidTypeId()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->publish( 999 );
    }

    /**
     * Test publish function
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::publish
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testPublishNoDraft()
    {
        $handler = $this->persistenceHandler->ContentTypeHandler();
        $handler->publish( 1 );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    private function getTypeCreateStruct( $status = Type::STATUS_DEFINED )
    {
        $struct = new CreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->status = $status;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        $struct->fieldDefinitions = array();
        $struct->groupIds = array( 1 );
        return $struct;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct
     */
    protected function getTypeUpdateStruct()
    {
        $struct = new UpdateStruct();
        $struct->modified = time();
        $struct->modifierId = 10;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        return $struct;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct
     */
    protected function getGroupCreateStruct()
    {
        $struct = new GroupCreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Media' );
        $struct->description = array( 'eng-GB' => 'Group for media content types' );
        $struct->identifier = 'media';
        return $struct;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    protected function getTypeFieldDefinition()
    {
        $field = new FieldDefinition();
        $field->identifier = 'title';
        $field->fieldType = 'ezstring';
        $field->position = 0;
        $field->isTranslatable = $field->isRequired = true;
        $field->isInfoCollector = false;
        $field->defaultValue = new FieldValue(
            array(
                "data" => "New Article"
            )
        );
        $field->name = array( 'eng-GB' => "Title" );
        $field->description = array( 'eng-GB' => "Title, used for headers, and url if short_title is empty" );
        return $field;
    }
}
