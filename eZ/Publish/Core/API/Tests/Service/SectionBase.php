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
    ezp\PublicAPI\Values\Content\SectionCreateStruct,
    ezp\PublicAPI\Values\Content\SectionUpdateStruct,
    eZ\Publish\Core\Base\Exception\NotFound;

/**
 * Test case for Section Service using InMemory storage class
 *
 */
abstract class SectionBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \ezp\PublicAPI\Values\Content\Section::__construct
     */
    public function testNewClass()
    {
        $section = new Section();
        self::assertEquals( $section->id, null );
        self::assertEquals( $section->identifier, null );
        self::assertEquals( $section->name, null );
    }

    /**
     * @expectedException eZ\Publish\Core\Base\Exception\PropertyNotFound
     * @covers \ezp\PublicAPI\Values\Content\Section::__get
     */
    public function testMissingProperty()
    {
        $section = new Section();
        $value = $section->notDefined;
    }

    /**
     * @expectedException eZ\Publish\Core\Base\Exception\PropertyPermission
     * @covers \ezp\PublicAPI\Values\Content\Section::__set
     */
    public function testReadOnlyProperty()
    {
    	self::markTestSkipped( 'ID is a public property, will not fail' );
        $section = new Section();
        $section->id = 22;
    }

    /**
     * Test service function for creating sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::create
     */
    public function testCreate()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $struct = new SectionCreateStruct();
        $struct->identifier = 'test';
        $struct->name = 'Test';

        $newSection = $service->createSection( $struct );
        //self::assertEquals( $newSection->id, 2 );
        self::assertEquals( $newSection->identifier, $struct->identifier );
        self::assertEquals( $newSection->name, $struct->name );
    }

    /**
     * Test service function for creating sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::create
     * @expectedException \eZ\Publish\Core\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        self::markTestSkipped( "@todo: Re add when permissions are re added" );
        $service = $this->repository->getSectionService();
        $struct = new SectionCreateStruct();
        $struct->identifier = 'test';
        $struct->name = 'Test';

        $newSection = $service->createSection( $struct );
    }

    /**
     * Test service function for loading sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::load
     */
    public function testLoad()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $section = $service->loadSection( 1 );
        self::assertEquals( 1, $section->id );
        self::assertEquals( 'standard', $section->identifier );
        self::assertEquals( 'Standard', $section->name );
    }
    
    /**
     * Test service function for loading sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::loadSectionByIdentifier
     */
    public function testLoadByIdentifier()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $section = $service->loadSectionByIdentifier( 'standard' );
        self::assertEquals( 1, $section->id );
        self::assertEquals( 'standard', $section->identifier );
        self::assertEquals( 'Standard', $section->name );
    }

    /**
     * Test service function for loading all sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::loadAll
     */
    public function testLoadAll()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $sections = $service->loadSections();

        self::assertInternalType( 'array', $sections );

        $sectionsCount = count( $sections );
        self::assertGreaterThan( 0, $sectionsCount );
    }

    /**
     * Test service function for loading sections
     *
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::load
     * @expectedException \eZ\Publish\Core\Base\Exception\NotFound
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getSectionService();
        $service->loadSection( 999 );
    }

    /**
     * Test service function for update sections
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::update
     */
    public function testUpdate()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $tempSection = $service->loadSection( 1 );
        $struct = new SectionUpdateStruct();
        $struct->identifier = 'test';
        $struct->name = 'Test';

        $section = $service->updateSection( $tempSection, $struct );

        self::assertEquals( $struct->identifier, $section->identifier );
        self::assertEquals( $struct->name, $section->name );
    }

    /**
     * Test service function for update sections
     *
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::update
     * @expectedException \eZ\Publish\Core\Base\Exception\Forbidden
     */
    public function testUpdateForbidden()
    {
        self::markTestSkipped( "@todo: Re add when permissions are re added" );
        $service = $this->repository->getSectionService();
        $section = $service->loadSection( 1 );
        $service->updateSection( $section, new SectionUpdateStruct() );
    }

    /**
     * Test service function for deleting sections
     *
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::delete
     */
    public function testDelete()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $struct = new SectionCreateStruct();
        $struct->identifier = 'test';
        $struct->name = 'Test';

        $newSection = $service->createSection( $struct );
        $service->deleteSection( $newSection );

        try
        {
            $service->loadSection( $newSection->id );
            self::fail( 'Section is still returned after being deleted' );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test service function for counting the contents which section is assigned to
     *
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::delete
     */
    public function testCountAssignedContents()
    {
        //$this->repository->setCurrentUser( $this->repository->getUserService()->loadUser( 14 ) );
        $service = $this->repository->getSectionService();
        $section = $service->loadSection( 1 );
        $contentCount = $service->countAssignedContents( $section );
        
        self::assertInternalType( 'integer', $contentCount );
        self::assertGreaterThan( 0, $contentCount );
    }

    /**
     * Test service function for deleting sections
     *
     * @covers \ezp\Publish\PublicAPI\Content\SectionService::delete
     * @expectedException \eZ\Publish\Core\Base\Exception\Forbidden
     */
    public function testDeleteForbidden()
    {
        self::markTestSkipped( "@todo: Re add when permissions are re added" );
        $service = $this->repository->getSectionService();
        $struct = new SectionCreateStruct();
        $struct->identifier = 'test';
        $struct->name = 'Test';

        $newSection = $service->createSection( $struct );
        $service->deleteSection( $newSection );
    }
}
