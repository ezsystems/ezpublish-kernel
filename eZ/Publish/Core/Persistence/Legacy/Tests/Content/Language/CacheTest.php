<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\CacheTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache,
    eZ\Publish\SPI\Persistence\Content\Language,
    ezp\Base\Exception;

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
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::store
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
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::remove
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
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getById
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
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testGetByIdFailure()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        // $cache->store( $languageFixture );
        $cache->getById( $languageFixture->id );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getByLocale
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
     * @expectedException ezp\Base\Exception\NotFound
     */
    public function testGetByLocaleFailure()
    {
        $cache = $this->getCache();

        $languageFixture = $this->getLanguageFixture();

        // $cache->store( $languageFixture );
        $cache->getByLocale( $languageFixture->languageCode );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache::getAll
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

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
