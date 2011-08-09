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
     * Test for load function
     *
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
}
