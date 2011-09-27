<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Language\CachingLanguageHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Language;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content\Language,
    ezp\Base\Exception,
    ezp\Persistence\Storage\Legacy\Content\Language\CachingHandler;

/**
 * Test case for caching Language Handler
 */
class CachingLanguageHandlerTest extends TestCase
{
    /**
     * Language handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Inner language handler mock
     *
     * @var \ezp\Persistence\Content\Language\Handler
     */
    protected $innerHandlerMock;

    /**
     * Language cache mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Cache
     */
    protected $languageCacheMock;

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::__construct
     */
    public function testCtorPropertyInnerHandler()
    {
        $handler   = $this->getLanguageHandler();

        $this->assertAttributeSame(
            $this->getInnerLanguageHandlerMock(),
            'innerHandler',
            $handler
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::__construct
     */
    public function testCtorPropertyLanguageCache()
    {
        $handler   = $this->getLanguageHandler();

        $this->assertAttributeSame(
            $this->getLanguageCacheMock(),
            'languageCache',
            $handler
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::getById
     */
    public function testGetById()
    {
        $this->expectCacheInitialize();

        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects( $this->once() )
            ->method( 'getById' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( new Language() ) );

        $handler = $this->getLanguageHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Language',
            $handler->getById( 23 )
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::getByLocale
     */
    public function testGetByLocale()
    {
        $this->expectCacheInitialize();

        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects( $this->once() )
            ->method( 'getByLocale' )
            ->with( $this->equalTo( 'eng-GB' ) )
            ->will( $this->returnValue( new Language() ) );

        $handler = $this->getLanguageHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Language',
            $handler->getByLocale( 'eng-GB' )
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::create
     */
    public function testCreate()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $cacheMock = $this->getLanguageCacheMock();

        $languageFixture = $this->getLanguageFixture();

        $innerHandlerMock->expects( $this->once() )
            ->method( 'create' )
            ->with(
                $this->isInstanceOf(
                    'ezp\Persistence\Content\Language\CreateStruct'
                )
            )->will( $this->returnValue( $languageFixture ) );

        // Cache has been initialized before
        $cacheMock->expects( $this->at( 2 ) )
            ->method( 'store' )
            ->with( $this->equalTo( $languageFixture ) );

        $createStruct = $this->getCreateStructFixture();

        $result = $handler->create( $createStruct );

        $this->assertEquals(
            $languageFixture,
            $result
        );
    }

    /**
     * Returns a Language CreateStruct
     *
     * @return \ezp\Persistence\Content\Language\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        return new Language\CreateStruct();
    }

    /**
     * Returns a Language
     *
     * @return \ezp\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $language = new Language();
        $language->id = 8;
        $language->locale = 'de-DE';
        return $language;
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::update
     */
    public function testUpdate()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();

        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $cacheMock = $this->getLanguageCacheMock();

        $innerHandlerMock->expects( $this->once() )
            ->method( 'update' )
            ->with( $this->getLanguageFixture() );

        // Cache has been initialized before
        $cacheMock->expects( $this->at( 2 ) )
            ->method( 'store' )
            ->with( $this->getLanguageFixture() );

        $handler->update( $this->getLanguageFixture() );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::load
     */
    public function testLoad()
    {
        $this->expectCacheInitialize();

        $handler   = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects( $this->once() )
            ->method( 'getById' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( $this->getLanguageFixture() ) );

        $result = $handler->load( 2 );

        $this->assertEquals(
            $this->getLanguageFixture(),
            $result
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::load
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadFailure()
    {
        $this->expectCacheInitialize();

        $handler   = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects( $this->once() )
            ->method( 'getById' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->throwException(
                new Exception\NotFound( 'Language', 2 )
            ) );

        $result = $handler->load( 2 );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::loadAll
     */
    public function testLoadAll()
    {
        $this->expectCacheInitialize();

        $handler     = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects( $this->once() )
            ->method( 'getAll' )
            ->will( $this->returnValue( array() ) );

        $result = $handler->loadAll();

        $this->assertInternalType(
            'array',
            $result
        );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::delete
     */
    public function testDelete()
    {
        $this->expectCacheInitialize();

        $handler          = $this->getLanguageHandler();
        $cacheMock        = $this->getLanguageCacheMock();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();

        $innerHandlerMock->expects( $this->once() )
            ->method( 'delete' )
            ->with( $this->equalTo( 2 ) );

        $cacheMock->expects( $this->once() )
            ->method( 'remove' )
            ->with( $this->equalTo( 2 ) );


        $result = $handler->delete( 2 );
    }

    /**
     * Returns the language handler to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $this->languageHandler = new CachingHandler(
                $this->getInnerLanguageHandlerMock(),
                $this->getLanguageCacheMock()
            );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a mock for the inner language handler
     *
     * @return \ezp\Persistence\Content\Language\Handler
     */
    protected function getInnerLanguageHandlerMock()
    {
        if ( !isset( $this->innerHandlerMock ) )
        {
            $this->innerHandlerMock = $this->getMock(
                'ezp\Persistence\Content\Language\Handler'
            );
        }
        return $this->innerHandlerMock;
    }

    /**
     * Returns a mock for the language cache
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\Cache
     */
    protected function getLanguageCacheMock()
    {
        if ( !isset( $this->languageCacheMock ) )
        {
            $this->languageCacheMock = $this->getMock(
                'ezp\Persistence\Storage\Legacy\Content\Language\Cache'
            );
        }
        return $this->languageCacheMock;
    }

    /**
     * Adds expectation for cache initialize to mocks
     *
     * @return void
     */
    protected function expectCacheInitialize()
    {
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $innerHandlerMock->expects( $this->once() )
            ->method( 'loadAll' )
            ->will( $this->returnValue( $this->getLanguagesFixture() ) );
    }

    /**
     * Returns an array with 2 languages
     *
     * @return \ezp\Persistence\Content\Language[]
     */
    protected function getLanguagesFixture()
    {
        $langUs = new Language();
        $langUs->id = 2;
        $langUs->locale = 'eng-US';
        $langUs->name = 'English (American)';
        $langUs->isEnabled = true;

        $langGb = new Language();
        $langGb->id = 4;
        $langGb->locale = 'eng-GB';
        $langGb->name = 'English (United Kingdom)';
        $langGb->isEnabled = true;

        return array( $langUs, $langGb );
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
