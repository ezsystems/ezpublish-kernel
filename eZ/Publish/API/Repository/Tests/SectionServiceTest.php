<?php

/**
 * File containing the SectionServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Section;
use Exception;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;

/**
 * Test case for operations in the SectionService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionService
 * @group integration
 * @group section
 */
class SectionServiceTest extends BaseTest
{
    private const SECTION_UNIQUE_KEY = 'uniqueKey';

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    /**
     * Tests that the required <b>ContentService::loadContentInfoByRemoteId()</b>
     * at least returns an object, because this method is utilized in several
     * tests,.
     */
    protected function setUp(): void
    {
        parent::setUp();

        try {
            // RemoteId of the "Media" page of an eZ Publish demo installation
            $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

            // Load the ContentService
            $contentService = $this->getRepository()->getContentService();

            // Load a content info instance
            $contentInfo = $contentService->loadContentInfoByRemoteId(
                $mediaRemoteId
            );

            if (false === is_object($contentInfo)) {
                $this->markTestSkipped(
                    'This test cannot be executed, because the utilized ' .
                    'ContentService::loadContentInfoByRemoteId() does not ' .
                    'return an object.'
                );
            }
        } catch (Exception $e) {
            $this->markTestSkipped(
                'This test cannot be executed, because the utilized ' .
                'ContentService::loadContentInfoByRemoteId() failed with ' .
                PHP_EOL . PHP_EOL .
                $e
            );
        }

        $repository = $this->getRepository(false);
        $this->permissionResolver = $repository->getPermissionResolver();
    }

    /**
     * Test for the newSectionCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::newSectionCreateStruct()
     */
    public function testNewSectionCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(SectionCreateStruct::class, $sectionCreate);
    }

    /**
     * Test for the createSection() method.
     *
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
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $section = $sectionService->createSection($sectionCreate);
        /* END: Use Case */

        $this->assertInstanceOf(Section::class, $section);
    }

    /**
     * Test for the createSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testNewSectionCreateStruct
     */
    public function testCreateSectionForUserWithSectionLimitation()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $this->createRoleWithPolicies('sectionCreator', [
            ['module' => 'section', 'function' => 'edit'],
        ]);

        $user = $this->createCustomUserWithLogin(
            'user',
            'user@example.com',
            'sectionCreators',
            'sectionCreator',
            new SectionLimitation(['limitationValues' => [1]])
        );

        $repository->getPermissionResolver()->setCurrentUserReference($user);

        $section = $sectionService->createSection($sectionCreate);
        /* END: Use Case */

        $this->assertInstanceOf(Section::class, $section);
        $this->assertSame(self::SECTION_UNIQUE_KEY, $section->identifier);
    }

    /**
     * Test for the createSection() method.
     *
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
        $sectionCreateOne->identifier = self::SECTION_UNIQUE_KEY;

        $sectionService->createSection($sectionCreateOne);

        $sectionCreateTwo = $sectionService->newSectionCreateStruct();
        $sectionCreateTwo->name = 'Test section two';
        $sectionCreateTwo->identifier = self::SECTION_UNIQUE_KEY;

        // This will fail, because identifier uniqueKey already exists.
        $sectionService->createSection($sectionCreateTwo);
        /* END: Use Case */
    }

    /**
     * Test for the loadSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSection()
    {
        $repository = $this->getRepository();

        $sectionId = $this->generateId('section', 2);
        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // Loads user section
        // $sectionId contains the corresponding ID
        $section = $sectionService->loadSection($sectionId);
        /* END: Use Case */

        $this->assertEquals('users', $section->identifier);
    }

    /**
     * Test for the loadSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadSectionThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentSectionId = $this->generateId('section', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // This call should fail with a NotFoundException
        // $nonExistentSectionId contains a section ID that is not known
        $sectionService->loadSection($nonExistentSectionId);
        /* END: Use Case */
    }

    /**
     * Test for the newSectionUpdateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::newSectionUpdateStruct()
     */
    public function testNewSectionUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(SectionUpdateStruct::class, $sectionUpdate);
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testNewSectionUpdateStruct
     */
    public function testUpdateSection()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection($standardSectionId);

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */

        // Verify that service returns an instance of Section
        $this->assertInstanceOf(Section::class, $updatedSection);

        // Verify that the service also persists the changes
        $updatedSection = $sectionService->loadSection($standardSectionId);

        $this->assertEquals('New section name', $updatedSection->name);
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSection
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testNewSectionUpdateStruct
     */
    public function testUpdateSectionForUserWithSectionLimitation()
    {
        $repository = $this->getRepository();
        $administratorUserId = $this->generateId('user', 14);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();
        $userService = $repository->getUserService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;
        $section = $sectionService->createSection($sectionCreate);

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        $this->createRoleWithPolicies('sectionCreator', [
            ['module' => 'section', 'function' => 'edit'],
        ]);
        $user = $this->createCustomUserWithLogin(
            'user',
            'user@example.com',
            'sectionCreators',
            'sectionCreator',
            new SectionLimitation(['limitationValues' => [$section->id]])
        );
        $this->permissionResolver->setCurrentUserReference($user);

        $updatedSection = $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */

        // Verify that service returns an instance of Section
        $this->assertInstanceOf(Section::class, $updatedSection);

        // Load section as an administrator
        $administratorUser = $userService->loadUser($administratorUserId);
        $this->permissionResolver->setCurrentUserReference($administratorUser);

        // Verify that the service also persists the changes
        $updatedSection = $sectionService->loadSection($section->id);

        $this->assertEquals('New section name', $updatedSection->name);
        $this->assertEquals('newUniqueKey', $updatedSection->identifier);
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionKeepsSectionIdentifierOnNameUpdate()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection($standardSectionId);
        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';

        $updatedSection = $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */

        $this->assertEquals('standard', $updatedSection->identifier);
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionWithSectionIdentifierOnNameUpdate()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection($standardSectionId);
        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';

        // section identifier remains the same
        $sectionUpdate->identifier = $section->identifier;

        $updatedSection = $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */

        $this->assertEquals('standard', $updatedSection->identifier);
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionKeepsSectionNameOnIdentifierUpdate()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection($standardSectionId);

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->identifier = 'newUniqueKey';

        $updatedSection = $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */

        $this->assertEquals('Standard', $updatedSection->name);
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $sectionService = $repository->getSectionService();

        // Create section with conflict identifier
        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Conflict section';
        $sectionCreate->identifier = 'conflictKey';

        $sectionService->createSection($sectionCreate);

        // Load an existing section and update to an existing identifier
        $section = $sectionService->loadSection($standardSectionId);

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->identifier = 'conflictKey';

        // This call should fail with an InvalidArgumentException
        $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the loadSections() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSections()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sections = $sectionService->loadSections();
        foreach ($sections as $section) {
            // Operate on all sections.
        }
        /* END: Use Case */

        $this->assertEquals(6, count($sections));
    }

    /**
     * Test for the loadSections() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testLoadSectionsReturnsDefaultSectionsByDefault()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();

        $this->assertEquals(
            [
                new Section(
                    [
                        'id' => $this->generateId('section', 1),
                        'name' => 'Standard',
                        'identifier' => 'standard',
                    ]
                ),
                new Section(
                    [
                        'id' => $this->generateId('section', 2),
                        'name' => 'Users',
                        'identifier' => 'users',
                    ]
                ),
                new Section(
                    [
                        'id' => $this->generateId('section', 3),
                        'name' => 'Media',
                        'identifier' => 'media',
                    ]
                ),
                new Section(
                    [
                        'id' => $this->generateId('section', 4),
                        'name' => 'Setup',
                        'identifier' => 'setup',
                    ]
                ),
                new Section(
                    [
                        'id' => $this->generateId('section', 5),
                        'name' => 'Design',
                        'identifier' => 'design',
                    ]
                ),
                new Section(
                    [
                        'id' => $this->generateId('section', 6),
                        'name' => 'Restricted',
                        'identifier' => '',
                    ]
                ),
            ],
            $sectionService->loadSections()
        );
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
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
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $sectionId = $sectionService->createSection($sectionCreate)->id;

        $section = $sectionService->loadSectionByIdentifier(self::SECTION_UNIQUE_KEY);
        /* END: Use Case */

        $this->assertEquals($sectionId, $section->id);
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadSectionByIdentifierThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        // This call should fail with a NotFoundException
        $sectionService->loadSectionByIdentifier('someUnknownSectionIdentifier');
        /* END: Use Case */
    }

    /**
     * Test for the countAssignedContents() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::countAssignedContents()
     */
    public function testCountAssignedContents()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $standardSection = $sectionService->loadSection($standardSectionId);

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
     * Test for the isSectionUsed() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::isSectionUsed()
     */
    public function testIsSectionUsed()
    {
        $repository = $this->getRepository();

        $sectionService = $repository->getSectionService();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $standardSection = $sectionService->loadSection($standardSectionId);

        $isSectionUsed = $sectionService->isSectionUsed(
            $standardSection
        );
        /* END: Use Case */

        $this->assertTrue(
            // Taken from the fixture
            $isSectionUsed
        );
    }

    /**
     * Test for the assignSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCountAssignedContents
     */
    public function testAssignSection()
    {
        $repository = $this->getRepository();
        $sectionService = $repository->getSectionService();

        $standardSectionId = $this->generateId('section', 1);
        $mediaSectionId = $this->generateId('section', 3);

        $beforeStandardCount = $sectionService->countAssignedContents(
            $sectionService->loadSection($standardSectionId)
        );
        $beforeMediaCount = $sectionService->countAssignedContents(
            $sectionService->loadSection($mediaSectionId)
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
        $section = $sectionService->loadSection($standardSectionId);

        // Assign Section to ContentInfo
        $sectionService->assignSection($contentInfo, $section);
        /* END: Use Case */

        $this->assertEquals(
            $beforeStandardCount + 1,
            $sectionService->countAssignedContents(
                $sectionService->loadSection($standardSectionId)
            )
        );
        $this->assertEquals(
            $beforeMediaCount - 1,
            $sectionService->countAssignedContents(
                $sectionService->loadSection($mediaSectionId)
            )
        );
    }

    /**
     * Test for the assignSectionToSubtree() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::assignSectionToSubtree()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testAssignSectionToSubtree()
    {
        $repository = $this->getRepository();
        $sectionService = $repository->getSectionService();

        $standardSectionId = $this->generateId('section', 1);
        $mediaSectionId = $this->generateId('section', 3);

        $beforeStandardCount = $sectionService->countAssignedContents(
            $sectionService->loadSection($standardSectionId)
        );

        $beforeMediaCount = $sectionService->countAssignedContents(
            $sectionService->loadSection($mediaSectionId)
        );

        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        // Load a location instance
        $location = $locationService->loadLocationByRemoteId($mediaRemoteId);

        // Load the "Standard" section
        $section = $sectionService->loadSection($standardSectionId);

        // Assign Section to ContentInfo
        $sectionService->assignSectionToSubtree($location, $section);

        /* END: Use Case */
        $this->assertEquals(
            $beforeStandardCount + 4,
            $sectionService->countAssignedContents(
                $sectionService->loadSection($standardSectionId)
            )
        );
        $this->assertEquals(
            $beforeMediaCount - 4,
            $sectionService->countAssignedContents(
                $sectionService->loadSection($mediaSectionId)
            )
        );
    }

    /**
     * Test for the countAssignedContents() method.
     *
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
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $section = $sectionService->createSection($sectionCreate);

        // The number of assigned contents should be zero
        $assignedContents = $sectionService->countAssignedContents($section);
        /* END: Use Case */

        $this->assertSame(0, $assignedContents);
    }

    /**
     * Test for the isSectionUsed() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::isSectionUsed()
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testCreateSection
     */
    public function testIsSectionUsedReturnsZeroByDefault()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $section = $sectionService->createSection($sectionCreate);

        // The number of assigned contents should be zero
        $isSectionUsed = $sectionService->isSectionUsed($section);
        /* END: Use Case */

        $this->assertFalse($isSectionUsed);
    }

    /**
     * Test for the deleteSection() method.
     *
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
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $section = $sectionService->createSection($sectionCreate);

        // Delete the newly created section
        $sectionService->deleteSection($section);
        /* END: Use Case */

        $this->assertEquals(6, count($sectionService->loadSections()));
    }

    /**
     * Test for the deleteSection() method.
     *
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
        $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

        $section = $sectionService->createSection($sectionCreate);

        // Delete the newly created section
        $sectionService->deleteSection($section);

        // This call should fail with a NotFoundException
        $sectionService->deleteSection($section);
        /* END: Use Case */
    }

    /**
     * Test for the deleteSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testAssignSection
     */
    public function testDeleteSectionThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        // $standardSectionId contains the ID of the "Standard" section in a eZ
        // Publish demo installation.

        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $contentService = $repository->getContentService();
        $sectionService = $repository->getSectionService();

        // Load the "Media" ContentInfo
        $contentInfo = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // Load the "Standard" section
        $section = $sectionService->loadSection($standardSectionId);

        // Assign "Media" to "Standard" section
        $sectionService->assignSection($contentInfo, $section);

        // This call should fail with a BadStateException, because there are assigned contents
        $sectionService->deleteSection($section);
        /* END: Use Case */
    }

    /**
     * Test for the createSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
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

        try {
            // Get a create struct and set some properties
            $sectionCreate = $sectionService->newSectionCreateStruct();
            $sectionCreate->name = 'Test Section';
            $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

            // Create a new section
            $sectionService->createSection($sectionCreate);
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try {
            // This call will fail with a not found exception
            $sectionService->loadSectionByIdentifier(self::SECTION_UNIQUE_KEY);
        } catch (NotFoundException $e) {
            // Expected execution path
        }
        /* END: Use Case */

        $this->assertTrue(isset($e), 'Can still load section after rollback.');
    }

    /**
     * Test for the createSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
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

        try {
            // Get a create struct and set some properties
            $sectionCreate = $sectionService->newSectionCreateStruct();
            $sectionCreate->name = 'Test Section';
            $sectionCreate->identifier = self::SECTION_UNIQUE_KEY;

            // Create a new section
            $sectionService->createSection($sectionCreate);

            // Commit all changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load new section
        $section = $sectionService->loadSectionByIdentifier(self::SECTION_UNIQUE_KEY);
        /* END: Use Case */

        $this->assertEquals(self::SECTION_UNIQUE_KEY, $section->identifier);
    }

    /**
     * Test for the createSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
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

        try {
            // Load standard section
            $section = $sectionService->loadSectionByIdentifier('standard');

            // Get an update struct and change section name
            $sectionUpdate = $sectionService->newSectionUpdateStruct();
            $sectionUpdate->name = 'My Standard';

            // Update section
            $sectionService->updateSection($section, $sectionUpdate);
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // Load updated section, name will still be "Standard"
        $updatedStandard = $sectionService->loadSectionByIdentifier('standard');
        /* END: Use Case */

        $this->assertEquals('Standard', $updatedStandard->name);
    }

    /**
     * Test for the createSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
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

        try {
            // Load standard section
            $section = $sectionService->loadSectionByIdentifier('standard');

            // Get an update struct and change section name
            $sectionUpdate = $sectionService->newSectionUpdateStruct();
            $sectionUpdate->name = 'My Standard';

            // Update section
            $sectionService->updateSection($section, $sectionUpdate);

            // Commit all changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load updated section, name will now be "My Standard"
        $updatedStandard = $sectionService->loadSectionByIdentifier('standard');
        /* END: Use Case */

        $this->assertEquals('My Standard', $updatedStandard->name);
    }
}
