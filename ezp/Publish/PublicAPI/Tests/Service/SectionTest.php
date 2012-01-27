<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\Service\SectionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Publish\PublicAPI\Tests\Service;
use ezp\Publish\PublicAPI\Tests\Service\Base as BaseServiceTest,
    ezp\PublicAPI\Values\Content\Section,
    ezp\Base\Exception\NotFound;

/**
 * Test case for Section Service using InMemory storage class
 *
 */
class SectionTest extends BaseServiceTest
{
    protected function setUp()
    {
        parent::setUp();
        self::markTestSkipped( "@todo SKipping?" );
    }

    /**
     * Test service function for creating sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::create
     */
    public function testCreate()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
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
     * Test service function for creating sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::create
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $newSection = $service->create( $section );
    }

    /**
     * Test service function for loading sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::load
     */
    public function testLoad()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
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
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getSectionService();
        $service->load( 999 );
    }

    /**
     * Test service function for update sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::update
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
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::update
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
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::delete
     */
    public function testDelete()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $section = new Section();
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
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test service function for deleting sections
     *
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::delete
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testDeleteForbidden()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->repository->getSectionService();
        $section = $service->create( $section );
        $service->delete( $section );
    }
}
