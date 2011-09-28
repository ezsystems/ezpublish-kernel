<?php
/**
 * File contains: ezp\Content\Tests\Service\SectionTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Section\Concrete as ConcreteSection,
    ezp\Base\Exception\NotFound;

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
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $section = new ConcreteSection();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $newSection = $service->create( $section );
        //self::assertEquals( $newSection->id, 2 );
        self::assertEquals( $newSection->identifier, $section->identifier );
        self::assertEquals( $newSection->name, $section->name );
    }

    /**
     * Test service function for creating sections
     * @covers \ezp\Content\Section\Service::create
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        $section = new ConcreteSection();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $newSection = $service->create( $section );
    }

    /**
     * Test service function for loading sections
     * @covers \ezp\Content\Section\Service::load
     */
    public function testLoad()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $section = new ConcreteSection();
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
     * @covers \ezp\Content\Section\Service::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getSectionService();
        $service->load( 999 );
    }

    /**
     * Test service function for update sections
     * @covers \ezp\Content\Section\Service::update
     */
    public function testUpdate()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $service = $this->repository->getSectionService();
        $tempSection = $service->load( 1 );
        $tempSection->identifier = 'test';
        $tempSection->name = 'Test';
        $service->update( $tempSection );
        $section = $service->load( 1 );
        self::assertEquals( $tempSection->identifier, $section->identifier );
        self::assertEquals( $tempSection->name, $section->name );
    }

    /**
     * Test service function for update sections
     *
     * @covers \ezp\Content\Section\Service::update
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testUpdateForbidden()
    {
        $service = $this->repository->getSectionService();
        $section = $service->load( 1 );
        $service->update( $section );
    }

    /**
     * Test service function for deleting sections
     *
     * @covers \ezp\Content\Section\Service::delete
     */
    public function testDelete()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $section = new ConcreteSection();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $section = $service->create( $section );
        $service->delete( $section );

        try
        {
            $service->load( $section->id );
            self::fail( 'Section is still returned after being deleted' );
        }
        catch ( NotFound $e ){}
    }

    /**
     * Test service function for deleting sections
     *
     * @covers \ezp\Content\Section\Service::delete
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testDeleteForbidden()
    {
        $section = new ConcreteSection();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $section = $service->create( $section );
        $service->delete( $section );
    }
}
