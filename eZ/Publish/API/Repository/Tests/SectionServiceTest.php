<?php
/**
 * File containing the SectionServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the SectionService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionService
 */
class SectionServiceTest extends BaseTest
{
    /**
     * Test for the newSectionCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::newSectionCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSectionService
     */
    public function testNewSectionCreateStruct()
    {
        $repository = $this->getRepository();

        ///* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        ///* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\SectionCreateStruct', $sectionCreate );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testNewSectionCreateStruct
     */
    public function testCreateSection()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();

        $sectionCreate->name       = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection( $sectionCreate );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Section', $section );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @expectedException eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testCreateSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::createSection() is not implemented." );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testCreateSectionThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreateOne = $sectionService->newSectionCreateStruct();

        $sectionCreateOne->name       = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKey';

        $sectionService->createSection( $sectionCreateOne );

        $sectionCreateTwo = $sectionService->newSectionCreateStruct();

        $sectionCreateTwo->name       = 'Test section two';
        $sectionCreateTwo->identifier = 'uniqueKey';

        // This will fail, because identifier uniqueKey already exists.
        $sectionService->createSection( $sectionCreateTwo );
        /* END: Use Case */
    }

    /**
     * Test for the loadSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSection()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();

        $sectionCreate->name       = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionId = $sectionService->createSection( $sectionCreate )->id;

        $section = $sectionService->loadSection( $sectionId );
        /* END: Use Case */

        $this->assertEquals( 'uniqueKey', $section->identifier );
    }

    /**
     * Test for the loadSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadSectionThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // This call should fail with a NotFoundException
        $sectionService->loadSection( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::loadSection() is not implemented." );
    }

    /**
     * Test for the newSectionUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::newSectionUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSectionService
     */
    public function testNewSectionUpdateStruct()
    {
        $repository = $this->getRepository();

        ///* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        ///* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct', $sectionUpdate );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testNewSectionUpdateStruct
     */
    public function testUpdateSection()
    {
        $repository = $this->getRepository();

        $sectionService            = $repository->getSectionService();
        $sectionCreate             = $sectionService->newSectionCreateStruct();
        $sectionCreate->name       = 'Test section one';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionId = $sectionService->createSection( $sectionCreate )->id;

        ///* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $sectionId );

        $sectionUpdate = $sectionService->newSectionUpdateStruct();

        $sectionUpdate->name       = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        ///* END: Use Case */

        // Verify that service returns an instance of Section
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Section', $updatedSection );

        // Verify that the service also persists the changes
        $updatedSection = $sectionService->loadSection( $sectionId );

        $this->assertEquals( 'New section name', $updatedSection->name );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::updateSection() is not implemented." );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUpdateSectionThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for SectionService::updateSection() is not implemented." );
    }

    /**
     * Test for the loadSections() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * 
     */
    public function testLoadSections()
    {
        $this->markTestIncomplete( "Test for SectionService::loadSections() is not implemented." );
    }

    /**
     * Test for the loadSections() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSectionsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::loadSections() is not implemented." );
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     */
    public function testLoadSectionByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();

        $sectionCreate->name       = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionId = $sectionService->createSection( $sectionCreate )->id;

        $section = $sectionService->loadSectionByIdentifier( 'uniqueKey' );
        /* END: Use Case */

        $this->assertEquals( $sectionId, $section->id );
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadSectionByIdentifierThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // This call should fail with a NotFoundException
        $sectionService->loadSectionByIdentifier( 'someUnknownSectionIdentifier' );
        /* END: Use Case */
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSectionByIdentifierThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::loadSectionByIdentifier() is not implemented." );
    }

    /**
     * Test for the countAssignedContents() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::countAssignedContents()
     */
    public function testCountAssignedContents()
    {
        $this->markTestIncomplete( "Test for SectionService::countAssignedContents() is not implemented." );
    }

    /**
     * Test for the assignSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     */
    public function testAssignSection()
    {
        $this->markTestIncomplete( "Test for SectionService::assignSection() is not implemented." );
    }

    /**
     * Test for the assignSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::assignSection() is not implemented." );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * 
     */
    public function testDeleteSection()
    {
        $this->markTestIncomplete( "Test for SectionService::deleteSection() is not implemented." );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteSectionThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for SectionService::deleteSection() is not implemented." );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteSectionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for SectionService::deleteSection() is not implemented." );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteSectionThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for SectionService::deleteSection() is not implemented." );
    }
}
