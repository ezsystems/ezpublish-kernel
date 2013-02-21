<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\LanguageBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for Language Service
 */
abstract class LanguageBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__construct
     */
    public function testNewClass()
    {
        $language = new Language();

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'languageCode' => null,
                'name' => null,
                'enabled' => null
            ),
            $language
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $language = new Language();
            $value = $language->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFound $e )
        {
        }
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $language = new Language();
            $language->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__isset
     */
    public function testIsPropertySet()
    {
        $language = new Language();
        $value = isset( $language->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $language->id );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\Content\Language::__unset
     */
    public function testUnsetProperty()
    {
        $language = new Language( array( "id" => 2 ) );
        try
        {
            unset( $language->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test service method for creating language
     * @covers \eZ\Publish\API\Repository\LanguageService::createLanguage
     */
    public function testCreateLanguage()
    {
        $service = $this->repository->getContentLanguageService();

        $languageCreateStruct = $service->newLanguageCreateStruct();
        $languageCreateStruct->languageCode = 'test-TEST';
        $languageCreateStruct->name = 'test';

        $newLanguage = $service->createLanguage( $languageCreateStruct );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $newLanguage );

        self::assertGreaterThan( 0, $newLanguage->id );

        $this->assertPropertiesCorrect(
            array(
                'languageCode' => $languageCreateStruct->languageCode,
                'name' => $languageCreateStruct->name,
                'enabled' => $languageCreateStruct->enabled
            ),
            $newLanguage
        );
    }

    /**
     * Test service method for creating language throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LanguageService::createLanguage
     */
    public function testCreateLanguageThrowsInvalidArgumentException()
    {
        $service = $this->repository->getContentLanguageService();

        $languageCreateStruct = $service->newLanguageCreateStruct();
        $languageCreateStruct->languageCode = 'eng-GB';
        $languageCreateStruct->name = 'English';

        $service->createLanguage( $languageCreateStruct );
    }

    /**
     * Test service method for updating language name
     * @covers \eZ\Publish\API\Repository\LanguageService::updateLanguageName
     */
    public function testUpdateLanguageName()
    {
        $languageService = $this->repository->getContentLanguageService();

        $language = $languageService->loadLanguage( 'eng-GB' );
        self::assertEquals( 'English (United Kingdom)', $language->name );

        $updatedLanguage = $languageService->updateLanguageName( $language, 'English' );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $updatedLanguage );

        $this->assertPropertiesCorrect(
            array(
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'name' => 'English',
                'enabled' => $language->enabled
            ),
            $updatedLanguage
        );
    }

    /**
     * Test service method for updating language name throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LanguageService::updateLanguageName
     */
    public function testUpdateLanguageNameThrowsInvalidArgumentException()
    {
        $languageService = $this->repository->getContentLanguageService();

        $language = $languageService->loadLanguage( 'eng-GB' );
        $languageService->updateLanguageName( $language, 1 );
    }

    /**
     * Test service method for enabling language
     * @covers \eZ\Publish\API\Repository\LanguageService::enableLanguage
     */
    public function testEnableLanguage()
    {
        $languageService = $this->repository->getContentLanguageService();

        $language = $languageService->loadLanguage( 'eng-GB' );
        self::assertEquals( true, $language->enabled );

        $updatedLanguage = $languageService->disableLanguage( $language );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $updatedLanguage );

        $this->assertPropertiesCorrect(
            array(
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'name' => $language->name,
                'enabled' => false
            ),
            $updatedLanguage
        );

        $finalLanguage = $languageService->enableLanguage( $updatedLanguage );

        $this->assertPropertiesCorrect(
            array(
                'id' => $updatedLanguage->id,
                'languageCode' => $updatedLanguage->languageCode,
                'name' => $updatedLanguage->name,
                'enabled' => true
            ),
            $finalLanguage
        );
    }

    /**
     * Test service method for disabling language
     * @covers \eZ\Publish\API\Repository\LanguageService::disableLanguage
     */
    public function testDisableLanguage()
    {
        $languageService = $this->repository->getContentLanguageService();

        $language = $languageService->loadLanguage( 'eng-GB' );
        self::assertEquals( true, $language->enabled );

        $updatedLanguage = $languageService->disableLanguage( $language );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $updatedLanguage );

        $this->assertPropertiesCorrect(
            array(
                'id' => $language->id,
                'languageCode' => $language->languageCode,
                'name' => $language->name,
                'enabled' => false
            ),
            $updatedLanguage
        );
    }

    /**
     * Test service method for loading language
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguage
     */
    public function testLoadLanguage()
    {
        $language = $this->repository->getContentLanguageService()->loadLanguage( 'eng-GB' );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $language );
        self::assertGreaterThan( 0, $language->id );

        $this->assertPropertiesCorrect(
            array(
                'languageCode' => 'eng-GB',
                'name' => 'English (United Kingdom)',
                'enabled' => true
            ),
            $language
        );
    }

    /**
     * Test service method for loading language throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguage
     */
    public function testLoadLanguageThrowsInvalidArgumentException()
    {
        $this->repository->getContentLanguageService()->loadLanguage( PHP_INT_MAX );
    }

    /**
     * Test service function for loading language throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguage
     */
    public function testLoadLanguageThrowsNotFoundException()
    {
        $this->repository->getContentLanguageService()->loadLanguage( 'ita-FR' );
    }

    /**
     * Test service method for loading all languages
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguages
     */
    public function testLoadLanguages()
    {
        $languageService = $this->repository->getContentLanguageService();

        $languages = $languageService->loadLanguages();

        self::assertInternalType( "array", $languages );
        self::assertNotEmpty( $languages );

        foreach ( $languages as $language )
        {
            self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $language );
        }
    }

    /**
     * Test service method for loading language by ID
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguageById
     */
    public function testLoadLanguageById()
    {
        $language = $this->repository->getContentLanguageService()->loadLanguageById( 2 );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Language', $language );

        $this->assertPropertiesCorrect(
            array(
                'id' => 2,
                'languageCode' => 'eng-US',
                'name' => 'English (American)',
                'enabled' => true
            ),
            $language
        );
    }

    /**
     * Test service method for loading language by ID throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguageById
     */
    public function testLoadLanguageByIdThrowsInvalidArgumentException()
    {
        $this->repository->getContentLanguageService()->loadLanguageById( 'test' );
    }

    /**
     * Test service method for loading language by ID throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguageById
     */
    public function testLoadLanguageByIdThrowsNotFoundException()
    {
        $this->repository->getContentLanguageService()->loadLanguageById( PHP_INT_MAX );
    }

    /**
     * Test service method for deleting language
     * @covers \eZ\Publish\API\Repository\LanguageService::deleteLanguage
     */
    public function testDeleteLanguage()
    {
        $languageService = $this->repository->getContentLanguageService();

        $languageCreateStruct = $languageService->newLanguageCreateStruct();
        $languageCreateStruct->name = 'test';
        $languageCreateStruct->languageCode = 'test-TEST';

        $newLanguage = $languageService->createLanguage( $languageCreateStruct );
        $languageService->deleteLanguage( $newLanguage );

        try
        {
            $languageService->loadLanguage( $languageCreateStruct->languageCode );
            self::fail( 'Language is still returned after being deleted' );
        }
        catch ( NotFoundException $e )
        {
        }
    }

    /**
     * Test service method for deleting language throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LanguageService::deleteLanguage
     */
    public function testDeleteLanguageThrowsInvalidArgumentException()
    {
        $languageService = $this->repository->getContentLanguageService();

        $language = $languageService->loadLanguage( 'eng-GB' );
        $languageService->deleteLanguage( $language );
    }

    /**
     * Test service method for fetching the default language code
     * @covers \eZ\Publish\API\Repository\LanguageService::getDefaultLanguageCode
     */
    public function testGetDefaultLanguageCode()
    {
        $defaultLanguageCode = $this->repository->getContentLanguageService()->getDefaultLanguageCode();

        self::assertEquals( 'eng-GB', $defaultLanguageCode );
    }

    /**
     * Test service method for creating a new language create struct object
     * @covers \eZ\Publish\API\Repository\LanguageService::newLanguageCreateStruct
     */
    public function testNewLanguageCreateStruct()
    {
        $languageCreateStruct = $this->repository->getContentLanguageService()->newLanguageCreateStruct();

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LanguageCreateStruct', $languageCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                'languageCode' => null,
                'name' => null,
                'enabled' => true
            ),
            $languageCreateStruct
        );
    }
}
