<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\SectionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\API\Repository\Values\Content\Section,
    eZ\Publish\API\Repository\Values\Content\SectionCreateStruct,
    eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    ezp\Base\Exception\PropertyPermission;

/**
 * Test case for Section Service using InMemory storage class
 *
 */
abstract class SectionBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__construct
     */
    public function testNewClass()
    {
        $section = new Section();
        self::assertEquals( $section->id, null );
        self::assertEquals( $section->identifier, null );
        self::assertEquals( $section->name, null );
    }

    /**
     * Test retrieving missing property
     * @expectedException ezp\Base\Exception\PropertyNotFound
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__get
     */
    public function testMissingProperty()
    {
        $section = new Section();
        $value = $section->notDefined;
    }

    /**
     * Test setting read only property
     * @expectedException ezp\Base\Exception\PropertyPermission
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__set
     */
    public function testReadOnlyProperty()
    {
        $section = new Section();
        $section->id = 22;
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__isset
     */
    public function testIsPropertySet()
    {
        $section = new Section();
        $value = isset( $section->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $section->id );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__unset
     */
    public function testUnsetProperty()
    {
        $section = new Section( array( 'id' => 1 ) );
        try
        {
            unset( $section->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyPermission $e ) {}
    }

    /**
     * Test service function for creating sections
     * @covers \eZ\Publish\Core\Repository\SectionService::create
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
     * @covers \eZ\Publish\Core\Repository\SectionService::create
     * @expectedException \eZ\Publish\Core\Base\Exceptions\Forbidden
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
     * @covers \eZ\Publish\Core\Repository\SectionService::load
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
     * @covers \eZ\Publish\Core\Repository\SectionService::loadSectionByIdentifier
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
     * @covers \eZ\Publish\Core\Repository\SectionService::loadAll
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
     * @covers \eZ\Publish\Core\Repository\SectionService::load
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getSectionService();
        $service->loadSection( 999 );
    }

    /**
     * Test service function for update sections
     * @covers \eZ\Publish\Core\Repository\SectionService::update
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
     * @covers \eZ\Publish\Core\Repository\SectionService::update
     * @expectedException \eZ\Publish\Core\Base\Exceptions\Forbidden
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
     * @covers \eZ\Publish\Core\Repository\SectionService::delete
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
        catch ( NotFoundException $e )
        {
        }
    }

    /**
     * Test service function for counting the contents which section is assigned to
     *
     * @covers \eZ\Publish\Core\Repository\SectionService::delete
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
     * @covers \eZ\Publish\Core\Repository\SectionService::delete
     * @expectedException \eZ\Publish\Core\Base\Exceptions\Forbidden
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
