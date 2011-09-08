<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Language\LanguageHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Language;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content\Language,
    ezp\Persistence\Storage\Legacy\Content\Language\Handler,
    ezp\Persistence\Storage\Legacy\Content\Language\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Language\Mapper;

/**
 * Test case for Language Handler
 */
class LanguageHandlerTest extends TestCase
{
    /**
     * Language handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Language gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Gateway
     */
    protected $gatewayMock;

    /**
     * Language mapper mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\Mapper
     */
    protected $mapperMock;

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::create
     */
    public function testCreate()
    {
        $handler = $this->getLanguageHandler();

        $mapperMock = $this->getMapperMock();
        $mapperMock->expects( $this->once() )
            ->method( 'createLanguageFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'ezp\Persistence\Content\Language\CreateStruct'
                )
            )->will( $this->returnValue( new Language() ) );

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'insertLanguage' )
            ->with(
                $this->isInstanceOf(
                    'ezp\Persistence\Content\Language'
                )
            )->will( $this->returnValue( 2 ) );

        $createStruct = $this->getCreateStructFixture();

        $result = $handler->create( $createStruct );

        $this->assertInstanceOf(
            'ezp\Persistence\Content\Language',
            $result
        );
        $this->assertEquals(
            2,
            $result->id
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
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::update
     */
    public function testUpdate()
    {
        $handler = $this->getLanguageHandler();

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects( $this->once() )
            ->method( 'updateLanguage' )
            ->with( $this->isInstanceOf( 'ezp\Persistence\Content\Language' ) );

        $handler->update( $this->getLanguageFixture() );
    }

    /**
     * Returns a Language
     *
     * @return \ezp\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        return new Language();
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::load
     */
    public function testLoad()
    {
        $handler     = $this->getLanguageHandler();
        $mapperMock  = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadLanguageData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractLanguagesFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new Language() ) ) );

        $result = $handler->load( 2 );

        $this->assertInstanceOf(
            'ezp\Persistence\Content\Language',
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
        $handler     = $this->getLanguageHandler();
        $mapperMock  = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadLanguageData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractLanguagesFromRows' )
            ->with( $this->equalTo( array() ) )
            // No language extracted
            ->will( $this->returnValue( array() ) );

        $result = $handler->load( 2 );
    }

    /**
     * @return void
     * @covers \ezp\Persistence\Storage\Legacy\Content\Language\Handler::loadAll
     */
    public function testLoadAll()
    {
        $handler     = $this->getLanguageHandler();
        $mapperMock  = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadAllLanguagesData' )
            ->will( $this->returnValue( array() ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractLanguagesFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new Language() ) ) );

        $result = $handler->loadAll();

        $this->assertType(
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
        $handler     = $this->getLanguageHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteLanguage' )
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
            $this->languageHandler = new Handler(
                $this->getGatewayMock(),
                $this->getMapperMock()
            );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a language mapper mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\Mapper
     */
    protected function getMapperMock()
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'ezp\Persistence\Storage\Legacy\Content\Language\Mapper'
            );
        }
        return $this->mapperMock;
    }

    /**
     * Returns a mock for the language gateway
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Language\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'ezp\Persistence\Storage\Legacy\Content\Language\Gateway'
            );
        }
        return $this->gatewayMock;
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
