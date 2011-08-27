<?php
/**
 * File contains: ezp\Content\Tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Section;

/**
 * Test case for Location class
 *
 */
class SectionTest extends BaseServiceTest
{
    /**
     * Test service function for creating sections
     * @covers \ezp\Content\Section\Service::create
     */
    public function testCreate()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $newSection = $service->create( $section );
        //self::assertEquals( $newSection->id, 2 );
        self::assertEquals( $newSection->identifier, $section->identifier );
        self::assertEquals( $newSection->name, $section->name );
    }

    /**
     * Test service function for deleting sections
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers \ezp\Content\Section\Service::delete
     */
    public function testDelete()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $section = $service->create( $section );
        $service->delete( $section->id );
        $service->load( $section->id );
    }

    /**
     * Test service function for loading sections
     * @covers \ezp\Content\Section\Service::load
     */
    public function testLoad()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $section = $service->create( $section );
        $newSection = $service->load( $section->id );
        //self::assertEquals( $newSection->id, 2 );
        self::assertEquals( $newSection->identifier, $section->identifier );
        self::assertEquals( $newSection->name, $section->name );

    }

    /**
     * Test service function for loading sections
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers \ezp\Content\Section\Service::load
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getSectionService();
        $service->load( 999 );
    }
}
