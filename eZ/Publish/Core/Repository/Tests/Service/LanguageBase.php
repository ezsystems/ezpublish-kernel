<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\LanguageBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\API\Repository\Exceptions\NotFoundException,
    eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;

/**
 * Test case for Language Service
 *
 */
abstract class LanguageBase extends BaseServiceTest
{
    /**
     * Test service function for creating language
     * @covers \ezp\PublicAPI\Values\Content\Section::__construct
     */
    public function testCreateLanguage()
    {
        $service = $this->repository->getContentLanguageService();
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $languageCreateStruct = new LanguageCreateStruct(
            array(
                'languageCode' => 'test-TEST',
                'name' => 'test'
            )
        );
        $newLanguage = $service->createLanguage( $languageCreateStruct );

        self::assertEquals( $newLanguage->languageCode, 'test-TEST' );
        self::assertEquals( $newLanguage->name, 'test' );
        self::assertTrue( $newLanguage->enabled );
    }

    /**
     * Test service function for creating language
     * @covers \ezp\Content\Language\Service::create
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        self::markTestSkipped( "@todo: Re add when permissions are re added" );
        $service = $this->repository->getContentLanguageService();
        $service->createLanguage( 'test-TEST', 'test' );
    }

    /**
     * Test service function for updating language
     * @covers \ezp\Content\Language\Service::update
     */
    public function testUpdateLanguageName()
    {
        $service = $this->repository->getContentLanguageService();
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $languageCreateStruct = new LanguageCreateStruct(
            array(
                'languageCode' => 'test-TEST',
                'name' => 'test'
            )
        );

        $language = $service->createLanguage( $languageCreateStruct );
        $sameLanguage = $service->updateLanguageName( $language, 'test name' );

        self::assertEquals( 'test name', $sameLanguage->name );
    }

    /**
     * Test service function for updating language
     * @covers \ezp\Content\Language\Service::update
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testUpdateForbidden()
    {
        self::markTestSkipped( "@todo: Re add when permissions are re added" );
    }

    /**
     * Test service function for deleting language
     *
     * @covers \ezp\Content\Language\Service::delete
     */
    public function testDelete()
    {
        self::markTestSkipped( "@todo: Re add when countContents method is added" );
        $service = $this->repository->getContentLanguageService();
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $newLanguage = $service->createLanguage( 'test-TEST', 'test' );
        $service->deleteLanguage( $newLanguage );
        try
        {
            $service->loadLanguage( $newLanguage->id );
            self::fail( 'Language is still returned after being deleted' );
        }
        catch ( NotFoundException $e )
        {
        }
    }

    /**
     * Test service function for deleting language
     *
     * @covers \ezp\Content\Language\Service::delete
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testDeleteForbidden()
    {
        self::markTestSkipped( "@todo: Re add when permissions are re added" );
    }

    /**
     * Test service function for loading language
     * @covers \ezp\Content\Language\Service::loadLanguageById
     */
    public function testLoadLanguageById()
    {
        $service = $this->repository->getContentLanguageService();
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $languageCreateStruct = new LanguageCreateStruct(
            array(
                'languageCode' => 'test-TEST',
                'name' => 'test'
            )
        );

        $language = $service->createLanguage( $languageCreateStruct );
        $sameLanguage = $service->loadLanguageById( $language->id );

        self::assertEquals( $sameLanguage->id, $language->id );
        self::assertEquals( $sameLanguage->languageCode, $language->languageCode );
        self::assertEquals( $sameLanguage->name, $language->name );
        self::assertEquals( $sameLanguage->enabled, $language->enabled );
    }

    /**
     * Test service function for loading language
     * @covers \ezp\Content\Language\Service::loadLanguage
     */
    public function testLoadLanguageByLanguageCode()
    {
        $service = $this->repository->getContentLanguageService();
        //Add when permission are done
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $languageCreateStruct = new LanguageCreateStruct(
            array(
                'languageCode' => 'test-TEST',
                'name' => 'test'
            )
        );

        $language = $service->createLanguage( $languageCreateStruct );
        $sameLanguage = $service->loadLanguage( 'test-TEST' );

        self::assertEquals( $language->id, $sameLanguage->id );
        self::assertEquals( $language->languageCode, $sameLanguage->languageCode );
        self::assertEquals( $language->name, $sameLanguage->name );
        self::assertEquals( $language->enabled, $sameLanguage->enabled );
    }

    /**
     * Test service function for loading language
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers \ezp\Content\Language\Service::loadLanguage
     */
    public function testLoadLanguageByLanguageCodeNotFound()
    {
        $service = $this->repository->getContentLanguageService();
        //Add when permission are done
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );

        $service->loadLanguage( 'ita-FR' );
    }

    /**
     * Test service function for loading all languages
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguages
     */
    public function testLoadAllLanguages()
    {
        $service = $this->repository->getContentLanguageService();
        //Add when permission are done
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $languages = $service->loadLanguages();
        foreach ( $languages as $language )
        {
            $service->deleteLanguage( $language );
        }

        $languageCreateStruct = new LanguageCreateStruct(
            array(
                'languageCode' => 'eng-GB',
                'name' => 'English (United Kingdom)',
                'enabled' => true
            )
        );
        $service->createLanguage( $languageCreateStruct );
        $languageCreateStruct = new LanguageCreateStruct(
            array(
                'languageCode' => 'eng-US',
                'name' => 'English (American)',
                'enabled' => false
            )
        );
        $service->createLanguage( $languageCreateStruct );
        $languages = $service->loadLanguages();

        self::assertInternalType( 'array', $languages );
        self::assertEquals( 2, count( $languages ) );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Language', $languages['eng-GB'] );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Language', $languages['eng-US'] );

        self::assertEquals( $languages['eng-GB']->id * 2, $languages['eng-US']->id );

        self::assertEquals( 'eng-GB', $languages['eng-GB']->languageCode );
        self::assertEquals( 'English (United Kingdom)', $languages['eng-GB']->name );
        self::assertTrue( $languages['eng-GB']->enabled );

        self::assertEquals( 'eng-US', $languages['eng-US']->languageCode );
        self::assertEquals( 'English (American)', $languages['eng-US']->name );
        self::assertFalse( $languages['eng-US']->enabled );
    }

    /**
     * Test service function for loading language
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\LanguageService::loadLanguageById
     */
    public function testLoadLanguageByIdNotFound()
    {
        $service = $this->repository->getContentLanguageService();
        $service->loadLanguageById( 999 );
    }

    /**
     * Test service function getDefaultLanguageCode
     *
     * @covers \eZ\Publish\API\Repository\LanguageService::getDefaultLanguageCode
     */
    public function testGetDefaultLanguageCode()
    {
        $service = $this->repository->getContentLanguageService();
        //Add when permission are done
        //$this->repository->setUser( $this->repository->getUserService()->load( 14 ) );

        $defaultLanguageCode = $service->getDefaultLanguageCode();

        self::assertEquals( 'eng-GB', $defaultLanguageCode );
    }
}
