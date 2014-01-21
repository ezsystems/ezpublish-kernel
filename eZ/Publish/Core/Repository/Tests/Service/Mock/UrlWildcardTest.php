<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\UrlWildcardTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard as SPIURLWildcard;
use eZ\Publish\API\Repository\Values\Content\URLWildcardTranslationResult;

/**
 * Mock Test case for UrlWildcard Service
 */
class UrlWildcardTest extends BaseServiceMockTest
{
    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::__construct
     */
    public function testConstructor()
    {
        $service = $this->getPartlyMockedURLWildcardService();

        self::assertAttributeSame( $this->getRepositoryMock(), "repository", $service );
        self::assertAttributeSame( $this->getPersistenceMockHandler( 'Content\\UrlWildcard\\Handler' ), "urlWildcardHandler", $service );
        self::assertAttributeSame( array(), "settings", $service );
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateThrowsUnauthorizedException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( false )
        );

        $mockedService->create( "lorem/ipsum", "opossum", true );
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateThrowsInvalidArgumentException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( true )
        );

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->will(
            $this->returnValue(
                array(
                    new SPIURLWildcard( array( "sourceUrl" => "/lorem/ipsum" ) )
                )
            )
        );

        $mockedService->create( "/lorem/ipsum", "opossum", true );
    }

    public function providerForTestCreateThrowsContentValidationException()
    {
        return array(
            array( "fruit", "food/{1}", true ),
            array( "fruit/*", "food/{2}", false ),
            array( "fruit/*/*", "food/{3}", true ),
        );
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @dataProvider providerForTestCreateThrowsContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateThrowsContentValidationException( $sourceUrl, $destinationUrl, $forward )
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( true )
        );

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->will(
            $this->returnValue( array() )
        );

        $mockedService->create( $sourceUrl, $destinationUrl, $forward );
    }

    public function providerForTestCreate()
    {
        return array(
            array( "fruit", "food", true ),
            array( " /fruit/ ", " /food/ ", true ),
            array( "/fruit/*", "/food", false ),
            array( "/fruit/*", "/food/{1}", true ),
            array( "/fruit/*/*", "/food/{1}", true ),
            array( "/fruit/*/*", "/food/{2}", true ),
            array( "/fruit/*/*", "/food/{1}/{2}", true ),
        );
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @dataProvider providerForTestCreate
     */
    public function testCreate( $sourceUrl, $destinationUrl, $forward )
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $sourceUrl = "/" . trim( $sourceUrl, "/ " );
        $destinationUrl = "/" . trim( $destinationUrl, "/ " );

        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( true )
        );

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "commit" );

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->will(
            $this->returnValue( array() )
        );

        $handlerMock->expects(
            $this->once()
        )->method(
            "create"
        )->with(
            $this->equalTo( $sourceUrl ),
            $this->equalTo( $destinationUrl ),
            $this->equalTo( $forward )
        )->will(
            $this->returnValue(
                new SPIURLWildcard(
                    array(
                        "id" => 123456,
                        "sourceUrl" => $sourceUrl,
                        "destinationUrl" => $destinationUrl,
                        "forward" => $forward
                    )
                )
            )
        );

        $urlWildCard = $mockedService->create( $sourceUrl, $destinationUrl, $forward );

        $this->assertEquals(
            new URLWildcard(
                array(
                    "id" => 123456,
                    "sourceUrl" => $sourceUrl,
                    "destinationUrl" => $destinationUrl,
                    "forward" => $forward
                )
            ),
            $urlWildCard
        );
    }

    /**
     * Test for the create() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \Exception
     */
    public function testCreateWithRollback()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( true )
        );

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "rollback" );

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->will(
            $this->returnValue( array() )
        );

        $sourceUrl = "/lorem";
        $destinationUrl = "/ipsum";
        $forward = true;

        $handlerMock->expects(
            $this->once()
        )->method(
            "create"
        )->with(
            $this->equalTo( $sourceUrl ),
            $this->equalTo( $destinationUrl ),
            $this->equalTo( $forward )
        )->will(
            $this->throwException( new \Exception )
        );

        $mockedService->create( $sourceUrl, $destinationUrl, $forward );
    }

    /**
     * Test for the remove() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemoveThrowsUnauthorizedException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( false )
        );

        $mockedService->remove( new URLWildcard() );
    }

    /**
     * Test for the remove() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     */
    public function testRemove()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( true )
        );

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "commit" );

        $handlerMock->expects(
            $this->once()
        )->method(
            "remove"
        )->with(
            $this->equalTo( "McBomb" )
        );

        $mockedService->remove( new URLWildcard( array( "id" => "McBomb" ) ) );
    }

    /**
     * Test for the remove() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \Exception
     */
    public function testRemoveWithRollback()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();
        $repositoryMock = $this->getRepositoryMock();

        $repositoryMock->expects(
            $this->once()
        )->method(
            "hasAccess"
        )->with(
            $this->equalTo( "content" ),
            $this->equalTo( "urltranslator" )
        )->will(
            $this->returnValue( true )
        );

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "rollback" );

        $handlerMock->expects(
            $this->once()
        )->method(
            "remove"
        )->with(
            $this->equalTo( "McBoo" )
        )->will(
            $this->throwException( new \Exception )
        );

        $mockedService->remove( new URLWildcard( array( "id" => "McBoo" ) ) );
    }

    /**
     * Test for the load() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \Exception
     */
    public function testLoadThrowsException()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "load"
        )->with(
            $this->equalTo( "Luigi" )
        )->will(
            $this->throwException( new \Exception )
        );

        $mockedService->load( "Luigi" );
    }

    /**
     * Test for the load() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     */
    public function testLoad()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "load"
        )->with(
            $this->equalTo( "Luigi" )
        )->will(
            $this->returnValue(
                new SPIURLWildcard(
                    array(
                        "id" => "Luigi",
                        "sourceUrl" => "this",
                        "destinationUrl" => "that",
                        "forward" => true
                    )
                )
            )
        );

        $urlWildcard = $mockedService->load( "Luigi" );

        $this->assertEquals(
            new URLWildcard(
                array(
                    "id" => "Luigi",
                    "sourceUrl" => "this",
                    "destinationUrl" => "that",
                    "forward" => true
                )
            ),
            $urlWildcard
        );
    }

    /**
     * Test for the loadAll() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::loadAll
     */
    public function testLoadAll()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->with(
            $this->equalTo( 0 ),
            $this->equalTo( -1 )
        )->will(
            $this->returnValue( array() )
        );

        $mockedService->loadAll();
    }

    /**
     * Test for the loadAll() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::loadAll
     */
    public function testLoadAllWithLimitAndOffset()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->with(
            $this->equalTo( 12 ),
            $this->equalTo( 34 )
        )->will(
            $this->returnValue(
                array(
                    new SPIURLWildcard(
                        array(
                            "id" => "Luigi",
                            "sourceUrl" => "this",
                            "destinationUrl" => "that",
                            "forward" => true
                        )
                    )
                )
            )
        );

        $urlWildcards = $mockedService->loadAll( 12, 34 );

        $this->assertEquals(
            array(
                new URLWildcard(
                    array(
                        "id" => "Luigi",
                        "sourceUrl" => "this",
                        "destinationUrl" => "that",
                        "forward" => true
                    )
                )
            ),
            $urlWildcards
        );
    }

    /**
     * @return array
     */
    public function providerForTestTranslateThrowsNotFoundException()
    {
        return array(
            array(
                array(
                    "sourceUrl" => "/fruit",
                    "destinationUrl" => "/food",
                    "forward" => true
                ),
                "/vegetable"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/apricot",
                    "destinationUrl" => "/food/apricot",
                    "forward" => true
                ),
                "/fruit/lemon"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*",
                    "destinationUrl" => "/food/{1}",
                    "forward" => true
                ),
                "/fruit"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*/*",
                    "destinationUrl" => "/food/{1}/{2}",
                    "forward" => true
                ),
                "/fruit/citrus"
            ),
        );
    }

    /**
     * Test for the translate() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::translate
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestTranslateThrowsNotFoundException
     */
    public function testTranslateThrowsNotFoundException( $createArray, $url )
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->with(
            $this->equalTo( 0 ),
            $this->equalTo( -1 )
        )->will(
            $this->returnValue( array( new SPIURLWildcard( $createArray ) ) )
        );

        $mockedService->translate( $url );
    }

    /**
     * @return array
     */
    public function providerForTestTranslate()
    {
        return array(
            array(
                array(
                    "sourceUrl" => "/fruit/apricot",
                    "destinationUrl" => "/food/apricot",
                    "forward" => true
                ),
                "/fruit/apricot",
                "/food/apricot"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*",
                    "destinationUrl" => "/food/{1}",
                    "forward" => true
                ),
                "/fruit/citrus",
                "/food/citrus"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*",
                    "destinationUrl" => "/food/{1}",
                    "forward" => true
                ),
                "/fruit/citrus/orange",
                "/food/citrus/orange"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*/*",
                    "destinationUrl" => "/food/{2}",
                    "forward" => true
                ),
                "/fruit/citrus/orange",
                "/food/orange"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*/*",
                    "destinationUrl" => "/food/{1}/{2}",
                    "forward" => true
                ),
                "/fruit/citrus/orange",
                "/food/citrus/orange"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*/pamplemousse",
                    "destinationUrl" => "/food/weird",
                    "forward" => true
                ),
                "/fruit/citrus/pamplemousse",
                "/food/weird"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*/pamplemousse",
                    "destinationUrl" => "/food/weird/{1}",
                    "forward" => true
                ),
                "/fruit/citrus/pamplemousse",
                "/food/weird/citrus"
            ),
            array(
                array(
                    "sourceUrl" => "/fruit/*/pamplemousse",
                    "destinationUrl" => "/food/weird/{1}",
                    "forward" => true
                ),
                "/fruit/citrus/yellow/pamplemousse",
                "/food/weird/citrus/yellow"
            ),
        );
    }

    /**
     * Test for the translate() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::translate
     * @dataProvider providerForTestTranslate
     */
    public function testTranslate( $createArray, $url, $uri )
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->with(
            $this->equalTo( 0 ),
            $this->equalTo( -1 )
        )->will(
            $this->returnValue( array( new SPIURLWildcard( $createArray ) ) )
        );

        $translationResult = $mockedService->translate( $url );

        $this->assertEquals(
            new URLWildcardTranslationResult(
                array(
                    "uri" => $uri,
                    "forward" => $createArray["forward"]
                )
            ),
            $translationResult
        );
    }

    /**
     * Test for the translate() method.
     *
     * @depends testConstructor
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::translate
     */
    public function testTranslateUsesLongestMatchingWildcard()
    {
        $mockedService = $this->getPartlyMockedURLWildcardService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->urlWildcardHandler();

        $handlerMock->expects(
            $this->once()
        )->method(
            "loadAll"
        )->with(
            $this->equalTo( 0 ),
            $this->equalTo( -1 )
        )->will(
            $this->returnValue(
                array(
                    new SPIURLWildcard(
                        array(
                            "sourceUrl" => "/something/*",
                            "destinationUrl" => "/short",
                            "forward" => true
                        )
                    ),
                    new SPIURLWildcard(
                        array(
                            "sourceUrl" => "/something/something/*",
                            "destinationUrl" => "/long",
                            "forward" => false
                        )
                    )
                )
            )
        );

        $translationResult = $mockedService->translate( "/something/something/thing" );

        $this->assertEquals(
            new URLWildcardTranslationResult(
                array(
                    "uri" => "/long",
                    "forward" => false
                )
            ),
            $translationResult
        );
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\URLWildcardService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedURLWildcardService( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\URLWildcardService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->urlWildcardHandler()
            )
        );
    }
}
