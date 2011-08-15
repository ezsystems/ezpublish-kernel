<?php
/**
 * File contains: ezp\Content\Tests\Type\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Type;
use ezp\Content\Tests\BaseServiceTest,
    ezp\Content\Type\Service,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group;

/**
 * Test case for Type service
 */
class ServiceTest extends BaseServiceTest
{
    /**
     * @var \ezp\Content\Type\Service
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getContentTypeService();
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createGroup
     */
    public function testCreateGroup()
    {
        $do = new Group();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do = $this->service->createGroup( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 0, count( $do->types ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
        $this->assertEquals( 2, $do->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createGroup
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateGroupException()
    {
        $do = new Group();
        $do->created = $do->modified = time();
        $this->service->createGroup( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateGroup
     */
    public function testUpdateGroup()
    {
        $do = $this->service->loadGroup( 1 );
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $this->service->updateGroup( $do );
        $do = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 1, count( $do->types ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateGroup
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testUpdateGroupException()
    {
        $do = $this->service->loadGroup( 1 );
        $do->identifier = null;
        $this->service->updateGroup( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::deleteGroup
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDeleteGroup()
    {
        $this->service->deleteGroup( 1 );
        $this->service->loadGroup( 1 );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadGroup
     */
    public function testLoadGroup()
    {
        $do = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 1, count( $do->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do->types[0] );
        $this->assertEquals( array( 'eng-GB' => "Content" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $list = $this->service->loadAllGroups();
        $this->assertEquals( 1, count( $list ) );
        $do = $list[0];
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 1, count( $do->types ) );
        $this->assertEquals( array( 'eng-GB' => "Content type group" ), $do->description );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     */
    public function testCreate()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do = $this->service->create( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 0, count( $do->groups ) );
        $this->assertEquals( 0, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     */
    public function testCreateWithField()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->fields[] = $field = new FieldDefinition( $do, 'ezstring' );
        $field->identifier = 'title';
        $field->defaultValue = 'New Test';
        $do = $this->service->create( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 0, count( $do->groups ) );
        $this->assertEquals( 1, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateException()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $this->service->create( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::update
     */
    public function testUpdate()
    {
        $do = $this->service->load( 1 );
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $this->service->update( $do );
        $do = $this->service->load( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::update
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testUpdateException()
    {
        $do = $this->service->load( 1 );
        $do->identifier = null;
        $this->service->update( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::delete
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDelete()
    {
        $this->service->delete( 1 );
        $this->service->load( 1 );
    }
    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::load
     */
    public function testLoad()
    {
        $type = $this->service->load( 1 );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( 1, count( $type->fields ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->types[0] );
        $this->assertEquals( $type->id, $type->groups[0]->types[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadByGroupId
     */
    public function testLoadByGroupId()
    {
        $list = $this->service->loadByGroupId( 1 );
        $this->assertEquals( 1, count( $list ) );

        $type = $list[0];
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( 1, count( $type->fields ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->types[0] );
        $this->assertEquals( $type->id, $type->groups[0]->types[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     */
    public function testLink()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';
        $newGroup = $this->service->createGroup( $newGroup );
        $type = $this->service->load( 1, 0 );

        $this->service->link( $type, $newGroup );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 2, count( $type->groups ) );
        $this->assertEquals( $newGroup->id, $type->groups[1]->id );
        $this->assertEquals( array( 'eng-GB' => 'Test' ), $type->groups[1]->name );
        $this->assertEquals( 'test', $type->groups[1]->identifier );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkGroupNotFound()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';

        $type = $this->service->load( 1, 0 );
        $this->service->link( $type, $newGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkTypeNotFound()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->delete( 1, 0 );
        $this->service->link( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testLinkTypeAlreadyPartOfGroup()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->link( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     */
    public function testUnLink()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';
        $newGroup = $this->service->createGroup( $newGroup );

        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );

        $this->service->link( $type, $newGroup );
        $this->service->unlink( $type, $existingGroup );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertEquals( $newGroup->id, $type->groups[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUnLinkTypeNotFound()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->delete( 1, 0 );
        $this->service->unlink( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkTypeNotPartOfGroup()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';
        $newGroup = $this->service->createGroup( $newGroup );

        $type = $this->service->load( 1, 0 );
        $this->service->unlink( $type, $newGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkLastGroup()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->unlink( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $field = new FieldDefinition( $type, 'ezstring' );
        $field->name = $field->description = array( 'eng-GB' => 'Test' );
        $field->defaultValue = $field->fieldGroup = '';
        $field->identifier = 'test';
        $field->isInfoCollector = $field->isRequired = $field->isTranslatable = true;
        $this->service->addFieldDefinition( $type, $field );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertEquals( 'test', $type->fields[1]->identifier );
        $this->assertEquals( 2, $type->fields[1]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testAddFieldDefinitionWithExistingFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->addFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAddFieldDefinitionWithUnExistingType()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->delete( $type->id, $type->status );

        $field = new FieldDefinition( $type, 'ezstring' );
        $field->name = $field->description = array( 'eng-GB' => 'Test' );
        $field->defaultValue = $field->fieldGroup = '';
        $field->identifier = 'test';
        $field->isInfoCollector = $field->isRequired = $field->isTranslatable = true;
        $this->service->addFieldDefinition( $type, $field );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     */
    public function testRemoveFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 0, count( $type->fields ) );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionWithUnExistingFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionWithUnExistingType()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->delete( $type->id, $type->status );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     */
    public function testUpdateFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $type->fields[0]->name = array( 'eng-GB' => 'New name' );
        $this->service->updateFieldDefinition( $type, $type->fields[0] );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 1, count( $type->fields ) );
        $this->assertEquals( array( 'eng-GB' => 'New name' ), $type->fields[0]->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionWithUnExistingFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $type->fields[0]->name = array( 'eng-GB' => 'New name' );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
        $this->service->updateFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionWithUnExistingType()
    {
        $type = $this->service->load( 1, 0 );
        $type->fields[0]->name = array( 'eng-GB' => 'New name' );
        $this->service->delete( $type->id, $type->status );
        $this->service->updateFieldDefinition( $type, $type->fields[0] );
    }
}
