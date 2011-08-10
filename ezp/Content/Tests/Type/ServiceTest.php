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
        $this->assertEquals( 1, count( $type->groups[0]->contentTypes ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->contentTypes[0] );
        $this->assertEquals( $type->id, $type->groups[0]->contentTypes[0]->id );
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
        $this->assertEquals( 1, count( $type->groups[0]->contentTypes ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->contentTypes[0] );
        $this->assertEquals( $type->id, $type->groups[0]->contentTypes[0]->id );
    }


    /**
     * @group contentTypeService
     * @covers \ezp\Content\Type\Service::loadGroup
     */
    public function testLoadGroup()
    {
        $group = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $group );
        $this->assertEquals( 1, count( $group->contentTypes ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $group->contentTypes[0] );
        $this->assertEquals( array( 'eng-GB' => "Content" ), $group->name );
    }
}
