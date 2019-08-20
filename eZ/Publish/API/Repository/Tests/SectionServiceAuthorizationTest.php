<?php

/**
 * File containing the SectionServiceAuthorizationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Test case for operations in the SectionService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SectionService
 * @group integration
 * @group authorization
 */
class SectionServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::createSection()
     */
    public function testCreateSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("User does not have access to 'edit' 'section");

        $sectionService->createSection($sectionCreate);
        /* END: Use Case */
    }

    /**
     * Test for the loadSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSection()
     */
    public function testLoadSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionId = $sectionService->createSection($sectionCreate)->id;

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("User does not have access to 'view' 'section");

        $sectionService->loadSection($sectionId);
        /* END: Use Case */
    }

    /**
     * Test for the updateSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::updateSection()
     */
    public function testUpdateSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        // $standardSectionId is the ID of the "Standard" section in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $section = $sectionService->loadSection($standardSectionId);

        $sectionUpdate = $sectionService->newSectionUpdateStruct();
        $sectionUpdate->name = 'New section name';
        $sectionUpdate->identifier = 'newUniqueKey';

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("User does not have access to 'edit' 'section");

        $sectionService->updateSection($section, $sectionUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the loadSections() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     */
    public function testLoadSectionsLoadsEmptyListForAnonymousUser()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        // Create some sections
        $sectionCreateOne = $sectionService->newSectionCreateStruct();
        $sectionCreateOne->name = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKeyOne';

        $sectionCreateTwo = $sectionService->newSectionCreateStruct();
        $sectionCreateTwo->name = 'Test section two';
        $sectionCreateTwo->identifier = 'uniqueKeyTwo';

        $sectionService->createSection($sectionCreateOne);
        $sectionService->createSection($sectionCreateTwo);

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $sections = $sectionService->loadSections();
        /* END: Use Case */

        $this->assertEquals([], $sections);
    }

    /**
     * Test for the loadSections() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSections()
     */
    public function testLoadSectionFiltersSections()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Publish demo installation.
        $sectionService = $repository->getSectionService();
        // Create some sections
        $sectionCreateOne = $sectionService->newSectionCreateStruct();
        $sectionCreateOne->name = 'Test section one';
        $sectionCreateOne->identifier = 'uniqueKeyOne';

        $sectionCreateTwo = $sectionService->newSectionCreateStruct();
        $sectionCreateTwo->name = 'Test section two';
        $sectionCreateTwo->identifier = 'uniqueKeyTwo';

        $expectedSection = $sectionService->createSection($sectionCreateOne);
        $sectionService->createSection($sectionCreateTwo);

        // Set user
        $this->createRoleWithPolicies('MediaUser', [
            ['module' => '*', 'function' => '*'],
        ]);
        $mediaUser = $this->createCustomUserWithLogin(
            'user',
            'user@example.com',
            'MediaUser',
            'MediaUser',
            new Limitation\SectionLimitation(['limitationValues' => [$expectedSection->id]])
        );

        $repository->getPermissionResolver()->setCurrentUserReference($mediaUser);

        $sections = $sectionService->loadSections();
        /* END: Use Case */

        // Only Sections the user has access to should be loaded
        $this->assertEquals([$expectedSection], $sections);
    }

    /**
     * Test for the loadSectionByIdentifier() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::loadSectionByIdentifier()
     */
    public function testLoadSectionByIdentifierThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $sectionService->createSection($sectionCreate);

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage("User does not have access to 'view' 'section");

        $sectionService->loadSectionByIdentifier('uniqueKey');
        /* END: Use Case */
    }

    /**
     * Test for the assignSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::assignSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $standardSectionId = $this->generateId('section', 1);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
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
        $section = $sectionService->loadSection($standardSectionId);

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $sectionService->assignSection($contentInfo, $section);
        /* END: Use Case */
    }

    /**
     * Test for the deleteSection() method.
     *
     * @see \eZ\Publish\API\Repository\SectionService::deleteSection()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteSectionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $sectionService = $repository->getSectionService();

        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->name = 'Test Section';
        $sectionCreate->identifier = 'uniqueKey';

        $section = $sectionService->createSection($sectionCreate);

        // Set anonymous user
        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $sectionService->deleteSection($section);
        /* END: Use Case */
    }
}
