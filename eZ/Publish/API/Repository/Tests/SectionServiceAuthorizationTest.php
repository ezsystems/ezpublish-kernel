<?php
/**
 * File containing the SectionServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the SectionService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 * @group authorization
 */
class SectionServiceAuthorizationTest extends BaseTest
{
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->createSection( $sectionCreate );
        /* END: Use Case */
    }

    /**
     * Test for the loadSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSection
     */
    public function testLoadSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionId = $sectionService->createSection( $sectionCreate )->id;

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->loadSection( $sectionId );
        /* END: Use Case */
    }

    /**
     * Test for the updateSection() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testUpdateSection
     */
    public function testUpdateSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId is the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection( $standardSectionId );

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->updateSection( $section, $sectionUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the loadSections() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\SectionServiceTest::testLoadSections
     */
    public function testLoadSectionsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        // Create some sections
        $sectionCreateOne = $sectionService->newSectionCreateStruct();
        $sectionCreateOne->name = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKeyOne';

        $sectionCreateTwo = $sectionService->newSectionCreateStruct();
        $sectionCreateTwo->name = 'Test section two';
        $sectionCreateTwo->identifier = 'uniqueKeyTwo';

        $sectionService->createSection( $sectionCreateOne );
        $sectionService->createSection( $sectionCreateTwo );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->loadSections();
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionService->createSection( $sectionCreate );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->loadSectionByIdentifier( 'uniqueKey' );
        /* END: Use Case */
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
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        // $standardSectionId is the ID of the "Standard" section in a eZ
        // Publish demo installation.

        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $userService = $repository->getUserService();
        $contentService = $repository->getContentService();
        $sectionService = $repository->getSectionService();

        // Load a content info instance
        $contentInfo = $contentService->loadContentInfoByRemoteId(
            $mediaRemoteId
        );

        // Load the "Standard" section
        $section = $sectionService->loadSection( $standardSectionId );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->assignSection( $contentInfo, $section );
        /* END: Use Case */
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection( $sectionCreate );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $sectionService->deleteSection( $section );
        /* END: Use Case */
    }
}
