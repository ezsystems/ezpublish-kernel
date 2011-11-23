<?php
/**
 * File contains: ezp\Content\Tests\Service\LanguageTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Language,
    ezp\Base\Exception\NotFound;

/**
 * Test case for Location class
 *
 */
class LanguageTest extends BaseServiceTest
{
    /**
     * Test service function for creating language
     * @covers \ezp\Content\Language\Service::create
     */
    public function testCreate()
    {
        $service = $this->repository->getContentLanguageService();
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $newLanguage = $service->create( 'test-TEST', 'test' );
        self::assertEquals( $newLanguage->locale, 'test-TEST' );
        self::assertEquals( $newLanguage->name, 'test' );
        self::assertTrue( $newLanguage->isEnabled );
    }

    /**
     * Test service function for creating language
     * @covers \ezp\Content\Language\Service::create
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testCreateForbidden()
    {
        $service = $this->repository->getContentLanguageService();
        $service->create( 'test-TEST', 'test' );
    }

    /**
     * Test service function for updating language
     * @covers \ezp\Content\Language\Service::update
     */
    public function testUpdate()
    {
        $service = $this->repository->getContentLanguageService();
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $language = $service->load( 2 );
        $language->locale = 'test-TEST';
        $language->name = 'test';
        $language->isEnabled = false;
        $service->update( $language );
        $sameLanguage = $service->load( 2 );
        self::assertEquals( $language->locale, $sameLanguage->locale );
        self::assertEquals( $language->name, $sameLanguage->name );
        self::assertEquals( $language->isEnabled, $sameLanguage->isEnabled );
    }

    /**
     * Test service function for updating language
     * @covers \ezp\Content\Language\Service::update
     * @expectedException \ezp\Base\Exception\Forbidden
     */
    public function testUpdateForbidden()
    {
        try
        {
            $service = $this->repository->getContentLanguageService();
            $language = $service->load( 2 );
        }
        catch ( \Exception $e )
        {
            self::fail( "Did not except any exceptions here, got: " . $e );
        }
        $service->update( $language );
    }

    /**
     * Test service function for deleting language
     *
     * @covers \ezp\Content\Language\Service::delete
     */
    public function testDelete()
    {
        $service = $this->repository->getContentLanguageService();
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $newLanguage = $service->create( 'test-TEST', 'test' );
        $service->delete( $newLanguage );
        try
        {
            $service->load( $newLanguage->id );
            self::fail( 'Language is still returned after being deleted' );
        }
        catch ( NotFound $e )
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
        $service = $this->repository->getContentLanguageService();
        try
        {
            $anon = $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
            $newLanguage = $service->create( 'test-TEST', 'test' );
        }
        catch ( \Exception $e )
        {
            self::fail( "Did not except any exceptions here, got: " . $e );
        }
        $this->repository->setUser( $anon );
        $service->delete( $newLanguage );
    }

    /**
     * Test service function for loading language
     * @covers \ezp\Content\Language\Service::load
     */
    public function testLoad()
    {
        $service = $this->repository->getContentLanguageService();
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $language = $service->create( 'test-TEST', 'test' );
        $newLanguage = $service->load( $language->id );
        self::assertEquals( $newLanguage->id, $language->id );
        self::assertEquals( $newLanguage->locale, $language->locale );
        self::assertEquals( $newLanguage->name, $language->name );
        self::assertEquals( $newLanguage->isEnabled, $language->isEnabled );
    }

    /**
     * Test service function for loading language
     * @covers \ezp\Content\Language\Service::loadByLocale
     */
    public function testLoadByLocale()
    {
        $service = $this->repository->getContentLanguageService();
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        $language = $service->create( 'test-TEST', 'test' );
        $newLanguage = $service->loadByLocale( $language->locale );
        self::assertEquals( $newLanguage->id, $language->id );
        self::assertEquals( $newLanguage->locale, $language->locale );
        self::assertEquals( $newLanguage->name, $language->name );
        self::assertEquals( $newLanguage->isEnabled, $language->isEnabled );
    }

    /**
     * Test service function for loading language
     * @covers \ezp\Content\Language\Service::loadAll
     */
    public function testLoadAll()
    {
        $service = $this->repository->getContentLanguageService();
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );
        foreach ( $service->loadAll() as $item )
        {
            $service->delete( $item );
        }

        $service->create( 'eng-GB', 'English (United Kingdom)' );
        $service->create( 'eng-US', 'English (American)' );
        $languages = $service->loadAll();
        self::assertEquals( 2, count( $languages ) );
        self::assertEquals( $languages['eng-GB']->id + 1, $languages['eng-US']->id );
        self::assertEquals( 'eng-GB', $languages['eng-GB']->locale );
        self::assertEquals( 'eng-US', $languages['eng-US']->locale );
    }

    /**
     * Test service function for loading language
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers \ezp\Content\Language\Service::load
     */
    public function testLoadNotFound()
    {
        $service = $this->repository->getContentLanguageService();
        $service->load( 999 );
    }
}
