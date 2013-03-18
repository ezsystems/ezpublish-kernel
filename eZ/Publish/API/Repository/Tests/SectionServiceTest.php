<?php
/**
 * File containing the SectionServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Section;

/**
 * Test case for operations in the SectionService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionService
 * @group integration
 * @group section
 */
class SectionServiceTest extends BaseTest
{
    /**
     * Tests that the required <b>ContentService::loadContentInfoByRemoteId()</b>
     * at least returns an object, because this method is utilized in several
     * tests,
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        try
        {
            // RemoteId of the "Media" page of an eZ Publish demo installation
            $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

            // Load the ContentService
            $contentService = $this->getRepository()->getContentService();

            // Load a content info instance
            $contentInfo = $contentService->loadContentInfoByRemoteId(
                $mediaRemoteId
            );

            if ( false === is_object( $contentInfo ) )
            {
                $this->markTestSkipped(
                    'This test cannot be executed, because the utilized ' .
                    'ContentService::loadContentInfoByRemoteId() does not ' .
                    'return an object.'
                );
            }
        }
        catch ( \Exception $e )
        {
            $this->markTestSkipped(
                'This test cannot be executed, because the utilized ' .
                'ContentService::loadContentInfoByRemoteId() failed with ' .
                PHP_EOL . PHP_EOL .
                $e
            );
        }

    }

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

        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionCreateStruct', $sectionCreate );
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
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection( $sectionCreate );
        /* END: Use Case */

        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section', $section );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testCreateSectionThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreateOne = $sectionService->newSectionCreateStruct();
        $sectionCreateOne->name = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKey';

        $sectionService->createSection( $sectionCreateOne );

        $sectionCreateTwo = $sectionService->newSectionCreateStruct();
        $sectionCreateTwo->name = 'Test section two';
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

        $sectionId = $this->generateId( 'section', 2 );
        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Loads user section
        // $sectionId contains the corresponding ID
        $section = $sectionService->loadSection( $sectionId );
        /* END: Use Case */

        $this->assertEquals( 'users', $section->identifier );
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

        $nonExistentSectionId = $this->generateId( 'section', PHP_INT_MAX );
        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // This call should fail with a NotFoundException
        // $nonExistentSectionId contains a section ID that is not known
        $sectionService->loadSection( $nonExistentSectionId );
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

        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionUpdateStruct', $sectionUpdate );
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

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $standardSectionId );

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */

        // Verify that service returns an instance of Section
        $this->assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section', $updatedSection );

        // Verify that the service also persists the changes
        $updatedSection = $sectionService->loadSection( $standardSectionId );

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
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $standardSectionId );
        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */

        $this->assertEquals( 'standard', $updatedSection->identifier );
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
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $standardSectionId );

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */

        $this->assertEquals( 'Standard', $updatedSection->name );
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        // Create section with conflict identifier
        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Conflict section';
        $sectionCreate->identifier = 'conflictKey';

        $sectionService->createSection( $sectionCreate );

        // Load an existing section and update to an existing identifier
        $section = $sectionService->loadSection( $standardSectionId );

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->identifier = 'conflictKey';

        // This call should fail with an InvalidArgumentException
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

        $sections = $sectionService->loadSections();
        foreach ( $sections as $section )
        {
            // Operate on all sections.
        }
        /* END: Use Case */

        $this->assertEquals( 6, count( $sections ) );
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
                        'id' => $this->generateId( 'section', 1 ),
                        'name' => 'Standard',
                        'identifier' => 'standard'
                    )
                ),
                new Section(
                    array(
                        'id' => $this->generateId( 'section', 2 ),
                        'name' => 'Users',
                        'identifier' => 'users'
                    )
                ),
                new Section(
                    array(
                        'id' => $this->generateId( 'section', 3 ),
                        'name' => 'Media',
                        'identifier' => 'media'
                    )
                ),
                new Section(
                    array(
                        'id' => $this->generateId( 'section', 4 ),
                        'name' => 'Setup',
                        'identifier' => 'setup'
                    )
                ),
                new Section(
                    array(
                        'id' => $this->generateId( 'section', 5 ),
                        'name' => 'Design',
                        'identifier' => 'design'
                    )
                ),
                new Section(
                    array(
                        'id' => $this->generateId( 'section', 6 ),
                        'name' => 'Restricted',
                        'identifier' => ''
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

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
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
     * Test for the countAssignedContents() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::countAssignedContents()
     *
     * @return void
     */
    public function testCountAssignedContents()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $standardSection = $sectionService->loadSection( $standardSectionId );

        $numberOfAssignedContent = $sectionService->countAssignedContents(
            $standardSection
        );
        /* END: Use Case */

        $this->assertEquals(
            2, // Taken from the fixture
            $numberOfAssignedContent
        );
    }

    /**
     * Test for the assignSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCountAssignedContents
     */
    public function testAssignSection()
    {
        $repository = $this->getRepository();
        $sectionService = $repository->getSectionService();

        $standardSectionId = $this->generateId( 'section', 1 );
        $mediaSectionId = $this->generateId( 'section', 3 );

        $beforeStandardCount = $sectionService->countAssignedContents(
            $sectionService->loadSection( $standardSectionId )
        );
        $beforeMediaCount = $sectionService->countAssignedContents(
            $sectionService->loadSection( $mediaSectionId )
        );

        /* BEGIN: Use Case */
        // $mediaSectionId contains the ID of the "Media" section in a eZ
        // Publish demo installation.

        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $contentService = $repository->getContentService();
        $sectionService = $repository->getSectionService();

        // Load a content info instance
        $contentInfo = $contentService->loadContentInfoByRemoteId(
            $mediaRemoteId
        );

        // Load the "Standard" section
        $section = $sectionService->loadSection( $standardSectionId );

        // Assign Section to ContentInfo
        $sectionService->assignSection( $contentInfo, $section );
        /* END: Use Case */

        $this->assertEquals(
            $beforeStandardCount + 1,
            $sectionService->countAssignedContents(
                $sectionService->loadSection( $standardSectionId )
            )
        );
        $this->assertEquals(
            $beforeMediaCount - 1,
            $sectionService->countAssignedContents(
                $sectionService->loadSection( $mediaSectionId )
            )
        );
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

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
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

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testDeleteSection
     */
    public function testDeleteSectionThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
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
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $contentService = $repository->getContentService();
        $sectionService = $repository->getSectionService();

        // Load the "Media" ContentInfo
        $contentInfo = $contentService->loadContentInfoByRemoteId( $mediaRemoteId );

        // Load the "Standard" section
        $section = $sectionService->loadSection( $standardSectionId );

        // Assign "Media" to "Standard" section
        $sectionService->assignSection( $contentInfo, $section );

        // This call should fail with a BadStateException, because there are assigned contents
        $sectionService->deleteSection( $section );
        /* END: Use Case */
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSectionByIdentifier
     */
    public function testCreateSectionInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Get a create struct and set some properties
            $sectionCreate = $sectionService->newSectionCreateStruct();
            $sectionCreate->name = 'Test Section';
            $sectionCreate->identifier = 'uniqueKey';

            // Create a new section
            $sectionService->createSection( $sectionCreate );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try
        {
            // This call will fail with a not found exception
            $sectionService->loadSectionByIdentifier( 'uniqueKey' );
        }
        catch ( NotFoundException $e )
        {
            // Expected execution path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Can still load section after rollback.' );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSectionByIdentifier
     */
    public function testCreateSectionInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Get a create struct and set some properties
            $sectionCreate = $sectionService->newSectionCreateStruct();
            $sectionCreate->name = 'Test Section';
            $sectionCreate->identifier = 'uniqueKey';

            // Create a new section
            $sectionService->createSection( $sectionCreate );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load new section
        $section = $sectionService->loadSectionByIdentifier( 'uniqueKey' );
        /* END: Use Case */

        $this->assertEquals( 'uniqueKey', $section->identifier );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSectionByIdentifier
     */
    public function testUpdateSectionInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Load standard section
            $section = $sectionService->loadSectionByIdentifier( 'standard' );

            // Get an update struct and change section name
            $sectionUpdate = $sectionService->newSectionUpdateStruct();
            $sectionUpdate->name = 'My Standard';

            // Update section
            $sectionService->updateSection( $section, $sectionUpdate );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // Load updated section, name will still be "Standard"
        $updatedStandard = $sectionService->loadSectionByIdentifier( 'standard' );
        /* END: Use Case */

        $this->assertEquals( 'Standard', $updatedStandard->name );
    }

    /**
     * Test for the createSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSectionByIdentifier
     */
    public function testUpdateSectionInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Load standard section
            $section = $sectionService->loadSectionByIdentifier( 'standard' );

            // Get an update struct and change section name
            $sectionUpdate = $sectionService->newSectionUpdateStruct();
            $sectionUpdate->name = 'My Standard';

            // Update section
            $sectionService->updateSection( $section, $sectionUpdate );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load updated section, name will now be "My Standard"
        $updatedStandard = $sectionService->loadSectionByIdentifier( 'standard' );
        /* END: Use Case */

        $this->assertEquals( 'My Standard', $updatedStandard->name );
    }
}
