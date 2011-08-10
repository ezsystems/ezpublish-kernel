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
     * @covers \ezp\Content\Type\Service::createGroup
     */
    public function testCreateGroup()
    {
        $group = new Group();
        $group->created = $group->modified = time();
        $group->creatorId = $group->modifierId = 14;
        $group->name = $group->description = array( 'eng-GB' => 'Test' );
        $group->identifier = 'test';
        $group = $this->service->createGroup( $group );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $group );
        $this->assertEquals( 0, count( $group->types ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $group->name );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::createGroup
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateGroupException()
    {
        $group = new Group();
        $group->created = $group->modified = time();
        $this->service->createGroup( $group );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::updateGroup
     */
    public function testUpdateGroup()
    {
        $group = $this->service->loadGroup( 1 );
        $group->created = $group->modified = time();
        $group->creatorId = $group->modifierId = 14;
        $group->name = $group->description = array( 'eng-GB' => 'Test' );
        $group->identifier = 'test';
        $this->service->updateGroup( $group );
        $group = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $group );
        $this->assertEquals( 1, count( $group->types ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $group->name );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::updateGroup
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testUpdateGroupException()
    {
        $group = $this->service->loadGroup( 1 );
        $group->identifier = null;
        $this->service->updateGroup( $group );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::deleteGroup
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDeleteGroup()
    {
        $this->service->deleteGroup( 1 );
        $this->service->loadGroup( 1 );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::loadGroup
     */
    public function testLoadGroup()
    {
        $group = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $group );
        $this->assertEquals( 1, count( $group->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $group->types[0] );
        $this->assertEquals( array( 'eng-GB' => "Content" ), $group->name );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $groups = $this->service->loadAllGroups();
        $this->assertEquals( 1, count( $groups ) );
        $group = $groups[0];
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $group );
        $this->assertEquals( 1, count( $group->types ) );
        $this->assertEquals( array( 'eng-GB' => "Content type group" ), $group->description );
    }

    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::load
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
     * @covers \ezp\Content\Type\Service::loadByGroupId
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
}
