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

        $createStruct               = new LanguageCreateStruct();
        $createStruct->enabled      = true;
        $createStruct->name         = 'English';
        $createStruct->languageCode = 'eng-US';

        $language = $languageService->createLanguage( $createStruct );
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

        $createStruct               = new LanguageCreateStruct();
        $createStruct->enabled      = true;
        $createStruct->name         = 'English';
        $createStruct->languageCode = 'eng-US';

        $language = $languageService->createLanguage( $createStruct );
        /* END: Use Case */

        $this->assertNotNull( $language->id );
    }

    /**
     * Test for the createLanguage() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::createLanguage()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
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
     */
    public function testCreateLanguageThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for LanguageService::createLanguage() is not implemented." );
    }

    /**
     * Test for the updateLanguageName() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::updateLanguageName()
     * 
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
     * 
     */
    public function testLoadLanguages()
    {
        $this->markTestIncomplete( "Test for LanguageService::loadLanguages() is not implemented." );
    }

    /**
     * Test for the loadLanguageById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguageById()
     * 
     */
    public function testLoadLanguageById()
    {
        $this->markTestIncomplete( "Test for LanguageService::loadLanguageById() is not implemented." );
    }

    /**
     * Test for the loadLanguageById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LanguageService::loadLanguageById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLanguageByIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for LanguageService::loadLanguageById() is not implemented." );
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

}
