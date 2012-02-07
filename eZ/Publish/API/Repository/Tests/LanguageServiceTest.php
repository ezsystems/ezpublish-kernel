<?php
/**
 * File containing the LanguageServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use \eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

/**
 * Test case for operations in the LanguageService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LanguageService
 */
class LanguageServiceTest extends BaseTest
{
    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentLanguageService
     */
    public function testCreateLanguage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate               = new LanguageCreateStruct();
        $languageCreate->enabled      = true;
        $languageCreate->name         = 'English';
        $languageCreate->languageCode = 'eng-US';

        $language = $languageService->createLanguage( $languageCreate );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Language', $language );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageSetsIdPropertyOnReturnedLanguage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate               = new LanguageCreateStruct();
        $languageCreate->enabled      = true;
        $languageCreate->name         = 'German';
        $languageCreate->languageCode = 'ger-DE';

        $language = $languageService->createLanguage( $languageCreate );
        /* END: Use Case */

        $this->assertNotNull( $language->id );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageSetsPropertiesFromCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate               = new LanguageCreateStruct();
        $languageCreate->enabled      = true;
        $languageCreate->name         = 'French';
        $languageCreate->languageCode = 'fra-FR';

        $language = $languageService->createLanguage( $languageCreate );
        /* END: Use Case */

        $this->assertEquals(
            array(
                true,
                'French',
                'fra-FR'
            ),
            array(
                $language->enabled,
                $language->name,
                $language->languageCode
            )
        );
    }

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
        $this->markTestIncomplete( "Test for LanguageService::createLanguage() is not implemented." );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testCreateLanguageThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate               = new LanguageCreateStruct();
        $languageCreate->enabled      = true;
        $languageCreate->name         = 'Norwegian';
        $languageCreate->languageCode = 'nor-NO';

        $languageService->createLanguage( $languageCreate );

        // This call should fail with an IllegalArgumentException, because nor-NO already exists
        $languageService->createLanguage( $languageCreate );
        /* END: Use Case */
    }

    /**
     * Test for the loadLanguageById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguageById()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testLoadLanguageById()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        $languageCreate               = new LanguageCreateStruct();
        $languageCreate->enabled      = false;
        $languageCreate->name         = 'English';
        $languageCreate->languageCode = 'eng-US';

        $languageId = $languageService->createLanguage( $languageCreate )->id;

        $language = $languageService->loadLanguageById( $languageId );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Language', $language );
    }

    /**
     * Test for the loadLanguageById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguageById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguageById
     */
    public function testLoadLanguageByIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for LanguageService::loadLanguageById() is not implemented." );
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testLoadLanguageById
     */
    public function testUpdateLanguageName()
    {
        $this->markTestIncomplete( "Test for LanguageService::updateLanguageName() is not implemented." );
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
        $this->markTestIncomplete( "Test for LanguageService::updateLanguageName() is not implemented." );
    }

    /**
     * Test for the enableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::enableLanguage()
     * 
     */
    public function testEnableLanguage()
    {
        $this->markTestIncomplete( "Test for LanguageService::enableLanguage() is not implemented." );
    }

    /**
     * Test for the enableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::enableLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testEnableLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for LanguageService::enableLanguage() is not implemented." );
    }

    /**
     * Test for the disableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::disableLanguage()
     * 
     */
    public function testDisableLanguage()
    {
        $this->markTestIncomplete( "Test for LanguageService::disableLanguage() is not implemented." );
    }

    /**
     * Test for the disableLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::disableLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDisableLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for LanguageService::disableLanguage() is not implemented." );
    }

    /**
     * Test for the loadLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguage()
     * 
     */
    public function testLoadLanguage()
    {
        $this->markTestIncomplete( "Test for LanguageService::loadLanguage() is not implemented." );
    }

    /**
     * Test for the loadLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLanguageThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for LanguageService::loadLanguage() is not implemented." );
    }

    /**
     * Test for the loadLanguages() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguages()
     * @depends eZ\Publish\API\Repository\Tests\LanguageServiceTest::testCreateLanguage
     */
    public function testLoadLanguages()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $languageService = $repository->getContentLanguageService();

        // Create some languages
        $languageEnglishCreate               = new LanguageCreateStruct();
        $languageEnglishCreate->enabled      = false;
        $languageEnglishCreate->name         = 'English';
        $languageEnglishCreate->languageCode = 'eng-US';

        $languageFrenchCreate               = new LanguageCreateStruct();
        $languageFrenchCreate->enabled      = false;
        $languageFrenchCreate->name         = 'French';
        $languageFrenchCreate->languageCode = 'fra-FR';

        $languageService->createLanguage( $languageEnglishCreate );
        $languageService->createLanguage( $languageFrenchCreate );

        $languages = $languageService->loadLanguages();
        foreach ( $languages as $language )
        {
            // Operate on each language
        }
        /* END: Use Case */

        $this->assertEquals( 2, count( $languages ) );
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * 
     */
    public function testDeleteLanguage()
    {
        $this->markTestIncomplete( "Test for LanguageService::deleteLanguage() is not implemented." );
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteLanguageThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for LanguageService::deleteLanguage() is not implemented." );
    }

    /**
     * Test for the deleteLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::deleteLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteLanguageThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for LanguageService::deleteLanguage() is not implemented." );
    }

    /**
     * Test for the getDefaultLanguageCode() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::getDefaultLanguageCode()
     * 
     */
    public function testGetDefaultLanguageCode()
    {
        $this->markTestIncomplete( "Test for LanguageService::getDefaultLanguageCode() is not implemented." );
    }

    /**
     * Helper method that creates a new language test fixture in the
     * API implementation under test.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Language
     */
    private function createLanguage()
    {
        $repository = $this->getRepository();

        $languageService = $repository->getContentLanguageService();
        $languageCreate  = new LanguageCreateStruct();

        $languageCreate->enabled      = true;
        $languageCreate->name         = 'English';
        $languageCreate->languageCode = 'eng-US';

        return $languageService->createLanguage( $languageCreate );
    }
}
