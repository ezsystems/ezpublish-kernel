<?php
/**
 * File containing the LanguageServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the LanguageService using in memory storage.
 *
 * @see \eZ\Publish\API\Repository\LanguageService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 * @group authorization
 */
class LanguageServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;
        $languageCreate->name = 'English (New Zealand)';
        $languageCreate->languageCode = 'eng-NZ';

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $languageService->createLanguage( $languageCreate );
        /* END: Use Case */
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testUpdateLanguageName
     */
    public function testUpdateLanguageNameThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = false;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $languageId = $languageService->createLanguage( $languageCreate )->id;

        $language = $languageService->loadLanguageById( $languageId );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $languageService->updateLanguageName( $language, 'New language name.' );
        /* END: Use Case */
    }

    /**
     * Test for the enableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::enableLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testEnableLanguage
     */
    public function testEnableLanguageThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = false;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreate );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $languageService->enableLanguage( $language );
        /* END: Use Case */
    }

    /**
     * Test for the disableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::disableLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testDisableLanguage
     */
    public function testDisableLanguageThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $languageService = $repository->getContentLanguageService();

        $languageCreate = $languageService->newLanguageCreateStruct();
        $languageCreate->enabled = true;
        $languageCreate->name = 'English';
        $languageCreate->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreate );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $languageService->disableLanguage( $language );
        /* END: Use Case */
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testDeleteLanguage
     */
    public function testDeleteLanguageThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $languageService = $repository->getContentLanguageService();

        $languageCreateEnglish = $languageService->newLanguageCreateStruct();
        $languageCreateEnglish->enabled = false;
        $languageCreateEnglish->name = 'English';
        $languageCreateEnglish->languageCode = 'eng-NZ';

        $language = $languageService->createLanguage( $languageCreateEnglish );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $languageService->deleteLanguage( $language );
        /* END: Use Case */
    }
}
