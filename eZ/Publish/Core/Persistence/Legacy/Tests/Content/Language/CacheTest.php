<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\CacheTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for caching Language Handler
 */
class CachingTest extends TestCase
{
    /**
     * Language cache
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected $cache;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::store
     *
     * @return void
     */
    public function testStore()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        $cache->store( $languageFixture );

        $this->assertAttributeEquals(
            array(
                $languageFixture->id => $languageFixture,
            ),
            'mapById',
            $cache
        );
        $this->assertAttributeEquals(
            array(
                $languageFixture->languageCode => $languageFixture,
            ),
            'mapByLocale',
            $cache
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::remove
     *
     * @return void
     */
    public function testRemove()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        $cache->store( $languageFixture );
        $cache->remove( $languageFixture->id );

        $this->assertAttributeEquals(
            array(),
            'mapById',
            $cache
        );
        $this->assertAttributeEquals(
            array(),
            'mapByLocale',
            $cache
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getById
     *
     * @return void
     */
    public function testGetById()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        $cache->store( $languageFixture );

        $this->assertSame(
            $languageFixture,
            $cache->getById( $languageFixture->id )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getById
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetByIdFailure()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        // $cache->store( $languageFixture );
        $cache->getById( $languageFixture->id );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getByLocale
     *
     * @return void
     */
    public function testGetByLocale()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        $cache->store( $languageFixture );

        $this->assertSame(
            $languageFixture,
            $cache->getByLocale( $languageFixture->languageCode )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getByLocale
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetByLocaleFailure()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        // $cache->store( $languageFixture );
        $cache->getByLocale( $languageFixture->languageCode );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getAll
     *
     * @return void
     */
    public function testGetAll()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        $cache->store( $languageFixture );

        $this->assertSame(
            array( $languageFixture->languageCode => $languageFixture ),
            $cache->getAll()
        );
    }

    /**
     * Returns the language cache to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected function getCache()
    {
        if ( !isset( $this->cache ) )
        {
            $this->cache = new Cache();
        }
        return $this->cache;
    }

    /**
     * Returns language fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $langUs = new Language();

        $langUs->id = 2;
        $langUs->languageCode = 'eng-US';
        $langUs->name = 'English (American)';
        $langUs->isEnabled = true;

        return $langUs;
    }
}
