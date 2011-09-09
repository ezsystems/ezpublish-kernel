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
     * Test service function for creating sections
     * @covers \ezp\Content\Language\Service::create
     */
    public function testCreate()
    {
        $service = $this->repository->getContentLanguageService();
        $newLanguage = $service->create( 'test-TEST', 'test' );
        //self::assertEquals( $newLanguage->id, 2 );
        self::assertEquals( $newLanguage->locale, 'test-TEST' );
        self::assertEquals( $newLanguage->name, 'test' );
        self::assertTrue( $newLanguage->isEnabled );
    }

    /**
     * Test service function for deleting sections
     *
     * @covers \ezp\Content\Language\Service::delete
     */
    public function testDelete()
    {
        $service = $this->repository->getContentLanguageService();
        $newLanguage = $service->create( 'test-TEST', 'test' );
        $service->delete( $newLanguage );
        try
        {
            $service->load( $newLanguage->id );
            self::fail( 'Language is still returned after being deleted' );
        }
        catch ( NotFound $e ){}
    }

    /**
     * Test service function for loading sections
     * @covers \ezp\Content\Language\Service::load
     */
    public function testLoad()
    {
        $service = $this->repository->getContentLanguageService();
        $language = $service->create( 'test-TEST', 'test' );
        $newLanguage = $service->load( $language->id );
        //self::assertEquals( $newLanguage->id, 2 );
        self::assertEquals( $newLanguage->id, $language->id );
        self::assertEquals( $newLanguage->locale, $language->locale );
        self::assertEquals( $newLanguage->name, $language->name );
        self::assertEquals( $newLanguage->isEnabled, $language->isEnabled );
    }

    /**
     * Test service function for loading sections
     * @covers \ezp\Content\Language\Service::loadAll
     */
    public function testLoadAll()
    {
        $service = $this->repository->getContentLanguageService();
        foreach ( $service->loadAll() as $item )
        {
            $service->delete( $item );
        }
 
        $service->create( 'eng-GB', 'English (United Kingdom)' );
        $service->create( 'eng-US', 'English (American)' );
        $languages = $service->loadAll();
        self::assertEquals( 2, count( $languages ) );
        self::assertEquals( $languages[0]->id +1, $languages[1]->id );
        self::assertEquals( 'eng-GB', $languages[0]->locale );
        self::assertEquals( 'eng-US', $languages[1]->locale );
    }

    /**
     * Test service function for loading sections
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
