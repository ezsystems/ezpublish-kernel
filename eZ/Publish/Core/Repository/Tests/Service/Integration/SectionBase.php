<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\SectionTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;

/**
 * Test case for Section Service using InMemory storage class
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

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'identifier' => null,
                'name' => null
            ),
            $section
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $section = new Section();
            $value = $section->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFound $e )
        {
        }
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\Content\Section::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $section = new Section();
            $section->id = 22;
            self::fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
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
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test service function for creating sections
     * @covers \eZ\Publish\Core\Repository\SectionService::createSection
     */
    public function testCreateSection()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionCreateStruct = $sectionService->newSectionCreateStruct();
        $sectionCreateStruct->identifier = 'test';
        $sectionCreateStruct->name = 'Test';

        $createdSection = $sectionService->createSection( $sectionCreateStruct );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section', $createdSection );
        self::assertGreaterThan( 0, $createdSection->id );

        $this->assertStructPropertiesCorrect(
            $sectionCreateStruct,
            $createdSection
        );
    }

    /**
     * Test service function for creating sections throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\Core\Repository\SectionService::createSection
     */
    public function testCreateSectionThrowsInvalidArgumentException()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionCreateStruct = $sectionService->newSectionCreateStruct();
        $sectionCreateStruct->identifier = 'standard';
        $sectionCreateStruct->name = 'Standard';

        $sectionService->createSection( $sectionCreateStruct );
    }

    /**
     * Test service function for updating sections
     * @covers \eZ\Publish\Core\Repository\SectionService::updateSection
     */
    public function testUpdateSection()
    {
        $sectionService = $this->repository->getSectionService();

        $loadedSection = $sectionService->loadSection( 1 );

        $sectionUpdateStruct = $sectionService->newSectionUpdateStruct();
        $sectionUpdateStruct->identifier = 'test';
        $sectionUpdateStruct->name = 'Test';

        $updatedSection = $sectionService->updateSection( $loadedSection, $sectionUpdateStruct );

        self::assertEquals( $loadedSection->id, $updatedSection->id );

        $this->assertStructPropertiesCorrect(
            $sectionUpdateStruct,
            $updatedSection
        );
    }

    /**
     * Test service function for updating sections
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\Core\Repository\SectionService::updateSection
     */
    public function testUpdateSectionThrowsInvalidArgumentException()
    {
        $sectionService = $this->repository->getSectionService();

        $loadedSection = $sectionService->loadSectionByIdentifier( 'standard' );

        $sectionUpdateStruct = $sectionService->newSectionUpdateStruct();
        $sectionUpdateStruct->identifier = 'media';
        $sectionUpdateStruct->name = 'Media';

        $sectionService->updateSection( $loadedSection, $sectionUpdateStruct );
    }

    /**
     * Test service function for loading sections
     * @covers \eZ\Publish\Core\Repository\SectionService::loadSection
     */
    public function testLoadSection()
    {
        $sectionService = $this->repository->getSectionService();

        $section = $sectionService->loadSection( 1 );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'name' => 'Standard',
                'identifier' => 'standard'
            ),
            $section
        );
    }

    /**
     * Test service function for loading sections throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\Core\Repository\SectionService::loadSection
     */
    public function testLoadSectionThrowsNotFoundException()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionService->loadSection( PHP_INT_MAX );
    }

    /**
     * Test service function for loading all sections
     * @covers \eZ\Publish\Core\Repository\SectionService::loadSections
     */
    public function testLoadSections()
    {
        $sections = $this->repository->getSectionService()->loadSections();

        self::assertInternalType( 'array', $sections );
        self::assertGreaterThan( 0, count( $sections ) );

        foreach ( $sections as $section )
        {
            self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Section', $section );
        }
    }

    /**
     * Test service function for loading section by identifier
     * @covers \eZ\Publish\Core\Repository\SectionService::loadSectionByIdentifier
     */
    public function testLoadSectionByIdentifier()
    {
        $sectionService = $this->repository->getSectionService();

        $section = $sectionService->loadSectionByIdentifier( 'standard' );

        $this->assertPropertiesCorrect(
            array(
                'id' => 1,
                'name' => 'Standard',
                'identifier' => 'standard'
            ),
            $section
        );
    }

    /**
     * Test service function for loading section by identifier throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\Core\Repository\SectionService::loadSectionByIdentifier
     */
    public function testLoadSectionByIdentifierThrowsNotFoundException()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionService->loadSectionByIdentifier( 'non-existing' );
    }

    /**
     * Test service function for counting content assigned to section
     * @covers \eZ\Publish\Core\Repository\SectionService::countAssignedContents
     */
    public function testCountAssignedContents()
    {
        $sectionService = $this->repository->getSectionService();

        $section = $sectionService->loadSection( 1 );
        $contentCount = $sectionService->countAssignedContents( $section );

        self::assertGreaterThan( 0, $contentCount );

        $sectionCreateStruct = $sectionService->newSectionCreateStruct();
        $sectionCreateStruct->identifier = 'test';
        $sectionCreateStruct->name = 'Test';

        $newSection = $sectionService->createSection( $sectionCreateStruct );
        $contentCount = $sectionService->countAssignedContents( $newSection );

        self::assertEquals( 0, $contentCount );
    }

    /**
     * Test service function for assigning section to content
     * @covers \eZ\Publish\Core\Repository\SectionService::assignSection
     */
    public function testAssignSection()
    {
        $sectionService = $this->repository->getSectionService();
        $contentService = $this->repository->getContentService();

        $section = $sectionService->loadSection( 1 );
        $contentInfo = $contentService->loadContentInfo( 4 );

        self::assertEquals( 2, $contentInfo->sectionId );

        $sectionService->assignSection( $contentInfo, $section );

        $contentInfo = $contentService->loadContentInfo( 4 );

        self::assertEquals( $section->id, $contentInfo->sectionId );
    }

    /**
     * Test service function for deleting sections
     * @covers \eZ\Publish\Core\Repository\SectionService::deleteSection
     */
    public function testDeleteSection()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionCreateStruct = $sectionService->newSectionCreateStruct();
        $sectionCreateStruct->identifier = 'test';
        $sectionCreateStruct->name = 'Test';

        $newSection = $sectionService->createSection( $sectionCreateStruct );
        $sectionService->deleteSection( $newSection );

        try
        {
            $sectionService->loadSection( $newSection->id );
            self::fail( 'Section is still returned after being deleted' );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test service function for deleting sections throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\Core\Repository\SectionService::deleteSection
     */
    public function testDeleteSectionThrowsNotFoundException()
    {
        $sectionService = $this->repository->getSectionService();

        $section = new Section( array( 'id' => PHP_INT_MAX ) );

        $sectionService->deleteSection( $section );
    }

    /**
     * Test service function for deleting sections throwing BadStateException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @covers \eZ\Publish\Core\Repository\SectionService::deleteSection
     */
    public function testDeleteSectionThrowsBadStateException()
    {
        $sectionService = $this->repository->getSectionService();

        $section = $sectionService->loadSection( 1 );

        $sectionService->deleteSection( $section );
    }

    /**
     * Test service function for creating new SectionCreateStruct
     * @covers \eZ\Publish\Core\Repository\SectionService::newSectionCreateStruct
     */
    public function testNewSectionCreateStruct()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionCreateStruct = $sectionService->newSectionCreateStruct();

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionCreateStruct', $sectionCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => null,
                'name' => null
            ),
            $sectionCreateStruct
        );
    }

    /**
     * Test service function for creating new SectionUpdateStruct
     * @covers \eZ\Publish\Core\Repository\SectionService::newSectionUpdateStruct
     */
    public function testNewSectionUpdateStruct()
    {
        $sectionService = $this->repository->getSectionService();

        $sectionUpdateStruct = $sectionService->newSectionUpdateStruct();

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SectionUpdateStruct', $sectionUpdateStruct );

        $this->assertPropertiesCorrect(
            array(
                'identifier' => null,
                'name' => null
            ),
            $sectionUpdateStruct
        );
    }
}
