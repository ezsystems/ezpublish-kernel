<?php
/**
 * File contains: ezp\Content\Tests\Service\TypeTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Type\Service,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group,
    ezp\Content\FieldType\Value,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Base\Exception\NotFound,
    ezp\Persistence\Content\Type as TypeValue,
    Exception;

/**
 * Test case for Type service
 */
class TypeTest extends BaseServiceTest
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
     */
    public function testDeleteGroup()
    {
        $group = $this->service->loadGroup( 1 );
        $this->service->deleteGroup( $group );
        try
        {
            $this->service->loadGroup( 1 );
            $this->fail( "Expected exception as group being loaded has been deleted" );

        }
        catch ( NotFound $e ){}
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
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do = $this->service->create( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( TypeValue::STATUS_DRAFT, $do->status );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( 0, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     */
    public function testCreateWithField()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do->fields[] = $field = new FieldDefinition( $do, 'ezstring' );
        $field->identifier = 'title';
        $field->setDefaultValue( new TextLineValue( 'New Test' ) );
        $do = $this->service->create( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 1, count( $do->groups ) );
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
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $this->service->create( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateWithoutGroup()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $this->service->create( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createAndPublish
     */
    public function testCreateAndPublish()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do = $this->service->createAndPublish( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( TypeValue::STATUS_DEFINED, $do->status );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( 0, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createAndPublish
     */
    public function testCreateAndPublishWithField()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do->fields[] = $field = new FieldDefinition( $do, 'ezstring' );
        $field->identifier = 'title';
        $field->setDefaultValue( new TextLineValue( 'New Test' ) );
        $do = $this->service->createAndPublish( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( 1, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createAndPublish
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateAndPublishException()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $this->service->createAndPublish( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createAndPublish
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateAndPublishWithoutGroup()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $this->service->createAndPublish( $do );
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
     */
    public function testDelete()
    {
        $type = $this->service->load( 1 );
        $this->service->delete( $type );

        try
        {
            $this->service->load( 1 );
            $this->fail( "Expected exception as type being loaded has been deleted" );
        }
        catch ( NotFound $e ){}
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::load
     */
    public function testLoad()
    {
        $type = $this->service->load( 1 );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( 2, count( $type->fields ) );
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
     * @covers ezp\Content\Type\Service::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadNotFound()
    {
        $this->service->load( 999 );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadByIdentifier
     */
    public function testLoadByIdentifier()
    {
        $type = $this->service->loadByIdentifier( 'folder' );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( 2, count( $type->fields ) );
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
     * @covers ezp\Content\Type\Service::loadByIdentifier
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadByIdentifierNotFound()
    {
        $this->service->loadByIdentifier( "hokuspokus" );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadDraft
     */
    public function testLoadDraft()
    {
        $copy = $this->service->copy( 10, 1 );
        $type = $this->service->loadDraft( $copy->id );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( $copy->id, $type->id );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->types[0] );
        $this->assertNotEquals( $type->id, $type->groups[0]->types[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadDraft
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadDraftNotFound()
    {
        $this->service->loadDraft( 1 );
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
        $this->assertEquals( 2, count( $type->fields ) );
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
     * @covers ezp\Content\Type\Service::loadDraftsByGroupId
     */
    public function testLoadDraftsByGroupId()
    {
        $list = $this->service->loadDraftsByGroupId( 1 );
        $this->assertEquals( 0, count( $list ) );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::copy
     */
    public function testCopy()
    {
        $type = $this->service->copy( 10, 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertStringStartsWith( 'folder_', $type->identifier );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertEquals( TypeValue::STATUS_DRAFT, $type->status );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        // newly created type should be draft
        $drafts = $this->service->loadDraftsByGroupId( $type->groups[0]->id );
        $this->assertEquals( 1, count( $drafts ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $drafts[0] );
        $this->assertEquals( $type->id, $drafts[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::copy
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCopyInvalidTypeId()
    {
        $this->service->copy( 10, 22 );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::copy
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCopyInvalidStatus()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do = $this->service->create( $do );
        $this->service->copy( 10, $do->id );
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
        $type = $this->service->load( 1 );

        $this->service->link( $type, $newGroup );

        $type = $this->service->load( 1 );
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

        $type = $this->service->load( 1 );
        $this->service->link( $type, $newGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkTypeNotFound()
    {
        $type = $this->service->load( 1 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->delete( $type );
        $this->service->link( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testLinkTypeAlreadyPartOfGroup()
    {
        $type = $this->service->load( 1 );
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

        $type = $this->service->load( 1 );
        $existingGroup = $this->service->loadGroup( 1 );

        $this->service->link( $type, $newGroup );
        $this->service->unlink( $type, $existingGroup );

        $type = $this->service->load( 1 );
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
        $type = $this->service->load( 1 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->delete( $type );
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

        $type = $this->service->load( 1 );
        $this->service->unlink( $type, $newGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkLastGroup()
    {
        $type = $this->service->load( 1 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->unlink( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $type = $this->service->load( 1 );
        $field = new FieldDefinition( $type, 'ezstring' );
        $field->name = $field->description = array( 'eng-GB' => 'Test' );
        $field->setDefaultValue( new TextLineValue( "" ) );
        $field->fieldGroup = '';
        $field->identifier = 'test';
        $field->isInfoCollector = $field->isRequired = $field->isTranslatable = true;
        $this->service->addFieldDefinition( $type, $field );

        $type = $this->service->load( 1 );
        $this->assertEquals( 3, count( $type->fields ) );
        $this->assertEquals( 'test', $type->fields[2]->identifier );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testAddFieldDefinitionWithExistingFieldDefinition()
    {
        $type = $this->service->load( 1 );
        $this->service->addFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAddFieldDefinitionWithUnExistingType()
    {
        $type = $this->service->load( 1 );
        $this->service->delete( $type );

        $field = new FieldDefinition( $type, 'ezstring' );
        $field->name = $field->description = array( 'eng-GB' => 'Test' );
        $field->setDefaultValue( new TextLineValue( "" ) );
        $field->fieldGroup = '';
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
        $type = $this->service->load( 1 );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );

        $type = $this->service->load( 1 );
        $this->assertEquals( 1, count( $type->fields ) );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionWithUnExistingFieldDefinition()
    {
        $type = $this->service->load( 1 );
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
        $type = $this->service->load( 1 );
        $this->service->delete( $type );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     */
    public function testUpdateFieldDefinition()
    {
        $type = $this->service->load( 1 );
        $type->fields[0]->name = array( 'eng-GB' => 'New name' );
        $this->service->updateFieldDefinition( $type, $type->fields[0] );

        $type = $this->service->load( 1 );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertEquals( array( 'eng-GB' => 'New name' ), $type->fields[0]->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionWithUnExistingFieldDefinition()
    {
        $type = $this->service->load( 1 );
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
        try
        {
            $type = $this->service->load( 1 );
            $fields = $type->getFields();
            $fields[0]->name = array( 'eng-GB' => 'New name' );
            $this->service->delete( $type );
        }
        catch ( Exception $e )
        {
            self::fail( "Did not expect any exception here, but got:" . $e );
        }
        $this->service->updateFieldDefinition( $type, $fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::publish
     */
    public function testPublish()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do = $this->service->create( $do );
        $this->service->publish( $do );
        $published = $this->service->load( $do->id );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $published );
        $this->assertEquals( TypeValue::STATUS_DEFINED, $published->status );
        $this->assertEquals( count( $do->groups ), count( $published->groups ) );
        $this->assertEquals( count( $do->fields ), count( $published->fields ) );
        $this->assertEquals( $do->name, $published->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::publish
     */
    public function testPublishWithFields()
    {
        $do = new ConcreteType();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do->fields[] = $field = new FieldDefinition( $do, 'ezstring' );
        $field->identifier = 'title';
        $field->setDefaultValue( new TextLineValue( 'New Test' ) );
        $do = $this->service->create( $do );
        $this->service->publish( $do );
        $published = $this->service->load( $do->id );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $published );
        $this->assertEquals( TypeValue::STATUS_DEFINED, $published->status );
        $this->assertEquals( count( $do->groups ), count( $published->groups ) );
        $this->assertEquals( count( $do->fields ), count( $published->fields ) );
        $this->assertEquals( $do->name, $published->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::publish
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testPublishInvalidTypeId()
    {
        $type = new ConcreteType();
        $struct = $type->getState( 'properties' );
        $struct->id = 999;
        $this->service->publish( $type );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::publish
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testPublishInvalidStatus()
    {
        $type = $this->service->load( 1 );
        $this->service->publish( $type );
    }
}
