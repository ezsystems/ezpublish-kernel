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

use eZ\Publish\API\Repository\Values\Content\Section;

/**
 * Test case for operations in the SectionService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionService
 * @group integration
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

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        /* END: Use Case */

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

        $sectionCreate             = $sectionService->newSectionCreateStruct();
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testCreateSectionThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreateOne             = $sectionService->newSectionCreateStruct();
        $sectionCreateOne->name       = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKey';

        $sectionService->createSection( $sectionCreateOne );

        $sectionCreateTwo             = $sectionService->newSectionCreateStruct();
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

        $sectionCreate             = $sectionService->newSectionCreateStruct();
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSectionService
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
     * Test for the newSectionUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::newSectionUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSectionService
     */
    public function testNewSectionUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        /* END: Use Case */

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
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $sectionId );

        $sectionUpdate             = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name       = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */

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
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionKeepsSectionIdentifierOnNameUpdate()
    {
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section             = $sectionService->loadSection( $sectionId );
        $sectionUpdate       = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */

        $this->assertEquals( 'uniqueKey', $updatedSection->identifier );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionKeepsSectionNameOnIdentifierUpdate()
    {
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $sectionId );

        $sectionUpdate             = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */

        $this->assertEquals( 'Test section one', $updatedSection->name );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionThrowsIllegalArgumentException()
    {
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Create section with conflict identifier
        $sectionCreate             = $sectionService->newSectionCreateStruct();
        $sectionCreate->name       = 'Conflict section';
        $sectionCreate->identifier = 'conflictKey';

        $sectionService->createSection( $sectionCreate );

        // Load an existing section and update to an existing identifier
        $section = $sectionService->loadSection( $sectionId );

        $sectionUpdate             = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->identifier = 'conflictKey';

        // This call should fail with an IllegalArgumentException
        $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the loadSections() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSections()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Create some sections
        $sectionCreateOne             = $sectionService->newSectionCreateStruct();
        $sectionCreateOne->name       = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKeyOne';

        $sectionCreateTwo             = $sectionService->newSectionCreateStruct();
        $sectionCreateTwo->name       = 'Test section two';
        $sectionCreateTwo->identifier = 'uniqueKeyTwo';

        $sectionService->createSection( $sectionCreateOne );
        $sectionService->createSection( $sectionCreateTwo );

        $sections = $sectionService->loadSections();
        foreach ( $sections as $section )
        {
            // Operate on all sections.
        }
        /* END: Use Case */

        $this->assertEquals( 8, count( $sections ) );
    }

    /**
     * Test for the loadSections() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSectionsReturnsDefaultSectionsByDefault()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();

        $this->assertEquals(
            array(
                new Section(
                    array(
                        'id'          =>  1,
                        'name'        =>  'Standard',
                        'identifier'  =>  'standard'
                    )
                ),
                new Section(
                    array(
                        'id'          =>  2,
                        'name'        =>  'Users',
                        'identifier'  =>  'users'
                    )
                ),
                new Section(
                    array(
                        'id'          =>  3,
                        'name'        =>  'Media',
                        'identifier'  =>  'media'
                    )
                ),
                new Section(
                    array(
                        'id'          =>  4,
                        'name'        =>  'Setup',
                        'identifier'  =>  'setup'
                    )
                ),
                new Section(
                    array(
                        'id'          =>  5,
                        'name'        =>  'Design',
                        'identifier'  =>  'design'
                    )
                ),
                new Section(
                    array(
                        'id'          =>  6,
                        'name'        =>  'Restricted',
                        'identifier'  =>  ''
                    )
                ),
            ),
            $sectionService->loadSections()
        );
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSectionByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate             = $sectionService->newSectionCreateStruct();
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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSectionService
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
     * Test for the assignSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testAssignSection()
    {
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        // TODO: This should be replaces with the original content service
        $contentInfo = $this->getMockForAbstractClass( '\eZ\Publish\API\Repository\Values\Content\ContentInfo', array( array( 'contentId' => 23 ) ) );

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $sectionId );

        $sectionService->assignSection( $contentInfo, $section );

        /* END: Use Case */

        // TODO: What to assert here? countAssignedContents() is not good, because that test depends on this test
        $this->assertEquals( 1, $sectionService->countAssignedContents( $section ) );
    }

    /**
     * Test for the countAssignedContents() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::countAssignedContents()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testAssignSection
     */
    public function testCountAssignedContents()
    {
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        // TODO: This should be replaces with the original content service
        $contentInfoOne = $this->getMockForAbstractClass( '\eZ\Publish\API\Repository\Values\Content\ContentInfo', array( array( 'contentId' => 23 ) ) );
        $contentInfoTwo = $this->getMockForAbstractClass( '\eZ\Publish\API\Repository\Values\Content\ContentInfo', array( array( 'contentId' => 42 ) ) );

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $sectionId );

        $sectionService->assignSection( $contentInfoOne, $section );
        $sectionService->assignSection( $contentInfoTwo, $section );

        /* END: Use Case */

        $this->assertEquals( 2, $sectionService->countAssignedContents( $section ) );
    }

    /**
     * Test for the countAssignedContents() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::countAssignedContents()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testCountAssignedContentsReturnsZeroByDefault()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate             = $sectionService->newSectionCreateStruct();
        $sectionCreate->name       = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection( $sectionCreate );

        // The number of assigned contents should be zero
        $assignedContents = $sectionService->countAssignedContents( $section );
        /* END: Use Case */

        $this->assertSame( 0, $assignedContents );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSections
     */
    public function testDeleteSection()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate             = $sectionService->newSectionCreateStruct();
        $sectionCreate->name       = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection( $sectionCreate );

        // Delete the newly created section
        $sectionService->deleteSection( $section );
        /* END: Use Case */

        $this->assertEquals( 6, count( $sectionService->loadSections() ) );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSections
     */
    public function testDeleteSectionAfterAssignedContentsWereDeleted()
    {
        $this->markTestIncomplete( "@TODO: Test for SectionService::deleteSection() is not implemented." );
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testDeleteSection
     */
    public function testDeleteSectionThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate             = $sectionService->newSectionCreateStruct();
        $sectionCreate->name       = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection( $sectionCreate );

        // Delete the newly created section
        $sectionService->deleteSection( $section );

        // This call should fail with a NotFoundException
        $sectionService->deleteSection( $section );
        /* END: Use Case */
    }

    /**
     * Test for the deleteSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testAssignSection
     */
    public function testDeleteSectionThrowsBadStateException()
    {
        $sectionId = $this->createSection()->id;

        $repository = $this->getRepository();

        // TODO: This should be replaces with the original content service
        $contentInfo = $this->getMockForAbstractClass( '\eZ\Publish\API\Repository\Values\Content\ContentInfo', array( array( 'contentId' => 23 ) ) );

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $sectionId );

        $sectionService->assignSection( $contentInfo, $section );

        // This call should fail with a BadStateException, because there are assigned contents
        $sectionService->deleteSection( $section );
        /* END: Use Case */
    }

    /**
     * Helper method that creates a new section in the API implementation under
     * test.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    private function createSection()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();
        $sectionCreate  = $sectionService->newSectionCreateStruct();

        $sectionCreate->name       = 'Test section one';
        $sectionCreate->identifier = 'uniqueKey';

        return $sectionService->createSection( $sectionCreate );
    }
}
