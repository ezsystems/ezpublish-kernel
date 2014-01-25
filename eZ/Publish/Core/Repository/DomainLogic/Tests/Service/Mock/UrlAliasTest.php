<?php
/**
 * File contains: eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\UrlAliasTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock;

use eZ\Publish\Core\Repository\DomainLogic\URLAliasService;
use eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\SPI\Persistence\Content\UrlAlias as SPIUrlAlias;
use eZ\Publish\API\Repository\Values\Content\UrlAlias;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\ForbiddenException;
use Exception;

/**
 * Mock test case for UrlAlias Service
 */
class UrlAliasTest extends BaseServiceMockTest
{
    /**
     * Test for the __construct() method.
     */
    public function testConstructor()
    {
        $repositoryMock = $this->getRepositoryMock();
        $languageServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\DomainLogic\\LanguageService",
            array(), array(), "", false
        );
        /** @var \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler $urlAliasHandler */
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $settings = array( "settings" );

        $languageServiceMock
            ->expects( $this->once() )
            ->method( "getPrioritizedLanguageCodeList" )
            ->will( $this->returnValue( array( "prioritizedLanguageList" ) ) );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getContentLanguageService" )
            ->will( $this->returnValue( $languageServiceMock ) );

        $service = new UrlALiasService(
            $repositoryMock,
            $urlAliasHandler,
            $settings
        );

        $this->assertAttributeSame(
            $repositoryMock,
            "repository",
            $service
        );

        $this->assertAttributeSame(
            $urlAliasHandler,
            "urlAliasHandler",
            $service
        );

        $this->assertAttributeSame(
            array(
                "settings",
                "showAllTranslations" => false,
                "prioritizedLanguageList" => array( "prioritizedLanguageList" )
            ),
            "settings",
            $service
        );
    }

    /**
     * Test for the load() method.
     */
    public function testLoad()
    {
        $mockedService = $this->getPartlyMockedURLAliasServiceService( array( "extractPath" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();

        $urlAliasHandlerMock
            ->expects( $this->once() )
            ->method( "loadUrlAlias" )
            ->with( 42 )
            ->will( $this->returnValue( new SPIUrlAlias ) );

        $mockedService
            ->expects( $this->once() )
            ->method( "extractPath" )
            ->with( $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias" ), null )
            ->will( $this->returnValue( "path" ) );

        $urlAlias = $mockedService->load( 42 );

        self::assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias",
            $urlAlias
        );
    }

    /**
     * Test for the load() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadThrowsNotFoundException()
    {
        $mockedService = $this->getPartlyMockedURLAliasServiceService( array( "extractPath" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();

        $urlAliasHandlerMock
            ->expects( $this->once() )
            ->method( "loadUrlAlias" )
            ->with( 42 )
            ->will( $this->throwException( new NotFoundException( "UrlAlias", 42 ) ) );

        $mockedService->load( 42 );
    }

    protected function getSpiUrlAlias()
    {
        $pathElement1 = array(
            "always-available" => true,
            "translations" => array(
                "cro-HR" => "jedan",
            )
        );
        $pathElement2 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "dva",
                "eng-GB" => "two",
            )
        );
        $pathElement3 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "tri",
                "eng-GB" => "three",
                "ger-DE" => "drei",
            )
        );
        return new SPIUrlAlias(
            array(
                "id" => "3",
                "pathData" => array( $pathElement1, $pathElement2, $pathElement3 ),
                "languageCodes" => array( "ger-DE" ),
                "alwaysAvailable" => false,
            )
        );
    }

    /**
     * Test for the load() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadThrowsNotFoundExceptionPath()
    {
        $spiUrlAlias = $this->getSpiUrlAlias();
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "fre-FR" ),
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $urlAliasHandlerMock = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandlerMock
            ->expects( $this->once() )
            ->method( "loadUrlAlias" )
            ->with( 42 )
            ->will( $this->returnValue( $spiUrlAlias ) );

        $urlAliasService->load( 42 );
    }

    /**
     * Test for the removeAliases() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testRemoveAliasesThrowsInvalidArgumentException()
    {
        $aliasList = array( new UrlAlias( array( "isCustom" => false ) ) );
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        $mockedService->removeAliases( $aliasList );
    }

    /**
     * Test for the removeAliases() method.
     */
    public function testRemoveAliases()
    {
        $aliasList = array( new UrlAlias( array( "isCustom" => true ) ) );
        $spiAliasList = array( new SPIUrlAlias( array( "isCustom" => true ) ) );
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();

        $repositoryMock
            ->expects( $this->once() )
            ->method( "beginTransaction" );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "commit" );

        $urlAliasHandlerMock
            ->expects( $this->once() )
            ->method( "removeURLAliases" )
            ->with( $spiAliasList );

        $mockedService->removeAliases( $aliasList );
    }

    /**
     * Test for the removeAliases() method.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testRemoveAliasesWithRollback()
    {
        $aliasList = array( new UrlAlias( array( "isCustom" => true ) ) );
        $spiAliasList = array( new SPIUrlAlias( array( "isCustom" => true ) ) );
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();

        $repositoryMock
            ->expects( $this->once() )
            ->method( "beginTransaction" );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "rollback" );

        $urlAliasHandlerMock
            ->expects( $this->once() )
            ->method( "removeURLAliases" )
            ->with( $spiAliasList )
            ->will( $this->throwException( new Exception( "Handler threw an exception" ) ) );

        $mockedService->removeAliases( $aliasList );
    }

    public function providerForTestListAutogeneratedLocationAliasesPath()
    {
        $pathElement1 = array(
            "always-available" => true,
            "translations" => array(
                "cro-HR" => "jedan",
            )
        );
        $pathElement2 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "dva",
                "eng-GB" => "two",
            )
        );
        $pathElement3 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "tri",
                "eng-GB" => "three",
                "ger-DE" => "drei",
            )
        );
        $pathData1 = array( $pathElement1 );
        $pathData2 = array( $pathElement1, $pathElement2 );
        $pathData3 = array( $pathElement1, $pathElement2, $pathElement3 );
        $spiUrlAliases1 = array(
            new SPIUrlAlias(
                array(
                    "id" => "1",
                    "pathData" => $pathData1,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => true,
                )
            )
        );
        $spiUrlAliases2 = array(
            new SPIUrlAlias(
                array(
                    "id" => "1",
                    "pathData" => $pathData2,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "id" => "2",
                    "pathData" => $pathData2,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            )
        );
        $spiUrlAliases3 = array(
            new SPIUrlAlias(
                array(
                    "id" => "1",
                    "pathData" => $pathData3,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "id" => "2",
                    "pathData" => $pathData3,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "id" => "3",
                    "pathData" => $pathData3,
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            )
        );

        return array(
            array(
                $spiUrlAliases1,
                array( "cro-HR" ),
                array(
                    "cro-HR" => "/jedan",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases1,
                array( "eng-GB" ),
                array(
                    "cro-HR" => "/jedan",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases1,
                array( "ger-DE" ),
                array(
                    "cro-HR" => "/jedan",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases1,
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array(
                    "cro-HR" => "/jedan",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "cro-HR" ),
                array(
                    "cro-HR" => "/jedan/dva",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "eng-GB" ),
                array(
                    "eng-GB" => "/jedan/two",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases2,
                array( "cro-HR", "eng-GB" ),
                array(
                    "cro-HR" => "/jedan/dva",
                    "eng-GB" => "/jedan/two",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "cro-HR", "ger-DE" ),
                array(
                    "cro-HR" => "/jedan/dva",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "eng-GB", "cro-HR" ),
                array(
                    "eng-GB" => "/jedan/two",
                    "cro-HR" => "/jedan/dva",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases2,
                array( "eng-GB", "ger-DE" ),
                array(
                    "eng-GB" => "/jedan/two",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases2,
                array( "ger-DE", "cro-HR" ),
                array(
                    "cro-HR" => "/jedan/dva",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "ger-DE", "eng-GB" ),
                array(
                    "eng-GB" => "/jedan/two",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases2,
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array(
                    "cro-HR" => "/jedan/dva",
                    "eng-GB" => "/jedan/two",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "cro-HR", "ger-DE", "eng-GB" ),
                array(
                    "cro-HR" => "/jedan/dva",
                    "eng-GB" => "/jedan/two",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "eng-GB", "cro-HR", "ger-DE" ),
                array(
                    "eng-GB" => "/jedan/two",
                    "cro-HR" => "/jedan/dva",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases2,
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "eng-GB" => "/jedan/two",
                    "cro-HR" => "/jedan/dva",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases2,
                array( "ger-DE", "cro-HR", "eng-GB" ),
                array(
                    "cro-HR" => "/jedan/dva",
                    "eng-GB" => "/jedan/two",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases2,
                array( "ger-DE", "eng-GB", "cro-HR" ),
                array(
                    "eng-GB" => "/jedan/two",
                    "cro-HR" => "/jedan/dva",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases3,
                array( "cro-HR" ),
                array(
                    "cro-HR" => "/jedan/dva/tri",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases3,
                array( "eng-GB" ),
                array(
                    "eng-GB" => "/jedan/two/three",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases3,
                array( "cro-HR", "eng-GB" ),
                array(
                    "cro-HR" => "/jedan/dva/tri",
                    "eng-GB" => "/jedan/dva/three",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases3,
                array( "cro-HR", "ger-DE" ),
                array(
                    "cro-HR" => "/jedan/dva/tri",
                    "ger-DE" => "/jedan/dva/drei",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases3,
                array( "eng-GB", "cro-HR" ),
                array(
                    "eng-GB" => "/jedan/two/three",
                    "cro-HR" => "/jedan/two/tri",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases3,
                array( "eng-GB", "ger-DE" ),
                array(
                    "eng-GB" => "/jedan/two/three",
                    "ger-DE" => "/jedan/two/drei",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases3,
                array( "ger-DE", "eng-GB" ),
                array(
                    "ger-DE" => "/jedan/two/drei",
                    "eng-GB" => "/jedan/two/three",
                ),
                "ger-DE",
            ),
            array(
                $spiUrlAliases3,
                array( "ger-DE", "cro-HR" ),
                array(
                    "ger-DE" => "/jedan/dva/drei",
                    "cro-HR" => "/jedan/dva/tri",
                ),
                "ger-DE",
            ),
            array(
                $spiUrlAliases3,
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array(
                    "cro-HR" => "/jedan/dva/tri",
                    "eng-GB" => "/jedan/dva/three",
                    "ger-DE" => "/jedan/dva/drei",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases3,
                array( "cro-HR", "ger-DE", "eng-GB" ),
                array(
                    "cro-HR" => "/jedan/dva/tri",
                    "ger-DE" => "/jedan/dva/drei",
                    "eng-GB" => "/jedan/dva/three",
                ),
                "cro-HR",
            ),
            array(
                $spiUrlAliases3,
                array( "eng-GB", "cro-HR", "ger-DE" ),
                array(
                    "eng-GB" => "/jedan/two/three",
                    "cro-HR" => "/jedan/two/tri",
                    "ger-DE" => "/jedan/two/drei",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases3,
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "eng-GB" => "/jedan/two/three",
                    "ger-DE" => "/jedan/two/drei",
                    "cro-HR" => "/jedan/two/tri",
                ),
                "eng-GB",
            ),
            array(
                $spiUrlAliases3,
                array( "ger-DE", "cro-HR", "eng-GB" ),
                array(
                    "ger-DE" => "/jedan/dva/drei",
                    "cro-HR" => "/jedan/dva/tri",
                    "eng-GB" => "/jedan/dva/three",
                ),
                "ger-DE",
            ),
            array(
                $spiUrlAliases3,
                array( "ger-DE", "eng-GB", "cro-HR" ),
                array(
                    "ger-DE" => "/jedan/two/drei",
                    "eng-GB" => "/jedan/two/three",
                    "cro-HR" => "/jedan/two/tri",
                ),
                "ger-DE",
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesPath
     */
    public function testListAutogeneratedLocationAliasesPath( $spiUrlAliases, $prioritizedLanguageCodes, $paths )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, null );

        self::assertEquals(
            count( $paths ),
            count( $urlAliases )
        );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            $pathKeys = array_keys( $paths );
            self::assertEquals(
                $paths[$pathKeys[$index]],
                $urlAlias->path
            );
            self::assertEquals(
                array( $pathKeys[$index] ),
                $urlAlias->languageCodes
            );
        }
    }

    /**
     * Test for the load() method.
     */
    public function testListLocationAliasesWithShowAllTranslations()
    {
        $pathElement1 = array(
            "always-available" => true,
            "translations" => array(
                "cro-HR" => "jedan",
            )
        );
        $pathElement2 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "dva",
                "eng-GB" => "two",
            )
        );
        $pathElement3 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "tri",
                "eng-GB" => "three",
                "ger-DE" => "drei",
            )
        );
        $spiUrlAlias = new SPIUrlAlias(
            array(
                "id" => "3",
                "pathData" => array( $pathElement1, $pathElement2, $pathElement3 ),
                "languageCodes" => array( "ger-DE" ),
                "alwaysAvailable" => false,
            )
        );
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "fre-FR" ),
            "showAllTranslations" => true,
        );
        $this->setConfiguration( $urlAliasService, $configuration );

        $urlAliasHandlerMock = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandlerMock->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( array( $spiUrlAlias ) )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, null );

        self::assertCount( 1, $urlAliases );
        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias", $urlAliases[0] );
        self::assertEquals( "/jedan/dva/tri", $urlAliases[0]->path );
    }

    public function providerForTestListAutogeneratedLocationAliasesEmpty()
    {
        $pathElement1 = array(
            "always-available" => true,
            "translations" => array(
                "cro-HR" => "/jedan",
            )
        );
        $pathElement2 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "dva",
                "eng-GB" => "two",
            )
        );
        $pathElement3 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "tri",
                "eng-GB" => "three",
                "ger-DE" => "drei",
            )
        );
        $pathData2 = array( $pathElement1, $pathElement2 );
        $pathData3 = array( $pathElement1, $pathElement2, $pathElement3 );
        $spiUrlAliases2 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData2,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData2,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            )
        );
        $spiUrlAliases3 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            )
        );

        return array(
            array(
                $spiUrlAliases2,
                array( "ger-DE" ),
            ),
            array(
                $spiUrlAliases3,
                array( "ger-DE" ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesEmpty
     */
    public function testListAutogeneratedLocationAliasesEmpty( $spiUrlAliases, $prioritizedLanguageCodes )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, null );

        self::assertEmpty( $urlAliases );
    }

    public function providerForTestListAutogeneratedLocationAliasesWithLanguageCodePath()
    {
        $pathElement1 = array(
            "always-available" => true,
            "translations" => array(
                "cro-HR" => "jedan",
            )
        );
        $pathElement2 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "dva",
                "eng-GB" => "two",
            )
        );
        $pathElement3 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "tri",
                "eng-GB" => "three",
                "ger-DE" => "drei",
            )
        );
        $pathData1 = array( $pathElement1 );
        $pathData2 = array( $pathElement1, $pathElement2 );
        $pathData3 = array( $pathElement1, $pathElement2, $pathElement3 );
        $spiUrlAliases1 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData1,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => true,
                )
            )
        );
        $spiUrlAliases2 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData2,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData2,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            )
        );
        $spiUrlAliases3 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            )
        );

        return array(
            array(
                $spiUrlAliases1,
                "cro-HR",
                array( "cro-HR" ),
                array(
                    "/jedan",
                ),
            ),
            array(
                $spiUrlAliases1,
                "cro-HR",
                array( "eng-GB" ),
                array(
                    "/jedan",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "eng-GB",
                array( "eng-GB" ),
                array(
                    "/jedan/two",
                ),
            ),
            array(
                $spiUrlAliases2,
                "eng-GB",
                array( "cro-HR", "eng-GB" ),
                array(
                    "/jedan/two",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "cro-HR", "ger-DE" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "eng-GB", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "eng-GB",
                array( "eng-GB", "ger-DE" ),
                array(
                    "/jedan/two",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "eng-GB",
                array( "ger-DE", "eng-GB" ),
                array(
                    "/jedan/two",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "eng-GB",
                array( "cro-HR", "ger-DE", "eng-GB" ),
                array(
                    "/jedan/two",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "eng-GB", "cro-HR", "ger-DE" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "ger-DE", "cro-HR", "eng-GB" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases2,
                "cro-HR",
                array( "ger-DE", "eng-GB", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases3,
                "cro-HR",
                array( "cro-HR" ),
                array(
                    "/jedan/dva/tri",
                ),
            ),
            array(
                $spiUrlAliases3,
                "eng-GB",
                array( "eng-GB" ),
                array(
                    "/jedan/two/three",
                ),
            ),
            array(
                $spiUrlAliases3,
                "eng-GB",
                array( "cro-HR", "eng-GB" ),
                array(
                    "/jedan/dva/three",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "cro-HR", "ger-DE" ),
                array(
                    "/jedan/dva/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "cro-HR",
                array( "eng-GB", "cro-HR" ),
                array(
                    "/jedan/two/tri",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "eng-GB", "ger-DE" ),
                array(
                    "/jedan/two/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "eng-GB",
                array( "ger-DE", "eng-GB" ),
                array(
                    "/jedan/two/three",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array(
                    "/jedan/dva/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "cro-HR", "ger-DE", "eng-GB" ),
                array(
                    "/jedan/dva/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "eng-GB", "cro-HR", "ger-DE" ),
                array(
                    "/jedan/two/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "/jedan/two/drei",
                ),
            ),
            array(
                $spiUrlAliases3,
                "eng-GB",
                array( "ger-DE", "cro-HR", "eng-GB" ),
                array(
                    "/jedan/dva/three",
                ),
            ),
            array(
                $spiUrlAliases3,
                "cro-HR",
                array( "ger-DE", "eng-GB", "cro-HR" ),
                array(
                    "/jedan/two/tri",
                ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesWithLanguageCodePath
     */
    public function testListAutogeneratedLocationAliasesWithLanguageCodePath(
        $spiUrlAliases,
        $languageCode,
        $prioritizedLanguageCodes,
        $paths
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, $languageCode );

        self::assertEquals(
            count( $paths ),
            count( $urlAliases )
        );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                $paths[$index],
                $urlAlias->path
            );
        }
    }

    public function providerForTestListAutogeneratedLocationAliasesWithLanguageCodeEmpty()
    {
        $pathElement1 = array(
            "always-available" => true,
            "translations" => array(
                "cro-HR" => "/jedan",
            )
        );
        $pathElement2 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "dva",
                "eng-GB" => "two",
            )
        );
        $pathElement3 = array(
            "always-available" => false,
            "translations" => array(
                "cro-HR" => "tri",
                "eng-GB" => "three",
                "ger-DE" => "drei",
            )
        );
        $pathData1 = array( $pathElement1 );
        $pathData2 = array( $pathElement1, $pathElement2 );
        $pathData3 = array( $pathElement1, $pathElement2, $pathElement3 );
        $spiUrlAliases1 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData1,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => true,
                )
            )
        );
        $spiUrlAliases2 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData2,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData2,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            )
        );
        $spiUrlAliases3 = array(
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => false,
                )
            ),
            new SPIUrlAlias(
                array(
                    "pathData" => $pathData3,
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            )
        );

        return array(
            array(
                $spiUrlAliases1,
                "eng-GB",
                array( "ger-DE" ),
            ),
            array(
                $spiUrlAliases1,
                "ger-DE",
                array( "cro-HR", "eng-GB", "ger-DE" ),
            ),
            array(
                $spiUrlAliases2,
                "eng-GB",
                array( "cro-HR" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "cro-HR", "eng-GB" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "cro-HR", "ger-DE" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "eng-GB", "ger-DE" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "ger-DE", "cro-HR" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "ger-DE", "eng-GB" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "cro-HR", "eng-GB", "ger-DE" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "cro-HR", "ger-DE", "eng-GB" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "eng-GB", "cro-HR", "ger-DE" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "eng-GB", "ger-DE", "cro-HR" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "ger-DE", "cro-HR", "eng-GB" ),
            ),
            array(
                $spiUrlAliases2,
                "ger-DE",
                array( "ger-DE", "eng-GB", "cro-HR" ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "cro-HR" ),
            ),
            array(
                $spiUrlAliases3,
                "cro-HR",
                array( "eng-GB" ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "cro-HR", "eng-GB" ),
            ),
            array(
                $spiUrlAliases3,
                "eng-GB",
                array( "cro-HR", "ger-DE" ),
            ),
            array(
                $spiUrlAliases3,
                "ger-DE",
                array( "eng-GB", "cro-HR" ),
            ),
            array(
                $spiUrlAliases3,
                "cro-HR",
                array( "eng-GB", "ger-DE" ),
            ),
            array(
                $spiUrlAliases3,
                "cro-HR",
                array( "ger-DE", "eng-GB" ),
            ),
            array(
                $spiUrlAliases3,
                "eng-GB",
                array( "ger-DE", "cro-HR" ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesWithLanguageCodeEmpty
     */
    public function testListAutogeneratedLocationAliasesWithLanguageCodeEmpty(
        $spiUrlAliases,
        $languageCode,
        $prioritizedLanguageCodes
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, $languageCode );

        self::assertEmpty( $urlAliases );
    }

    public function providerForTestListAutogeneratedLocationAliasesMultipleLanguagesPath()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "jedan",
                            )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-GB" => "dva",
                                "ger-DE" => "dva",
                            )
                        )
                    ),
                    "languageCodes" => array( "eng-GB", "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                array( "cro-HR", "ger-DE" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "eng-GB" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesMultipleLanguagesPath
     */
    public function testListAutogeneratedLocationAliasesMultipleLanguagesPath( $spiUrlAliases, $prioritizedLanguageCodes, $paths )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, null );

        self::assertEquals(
            count( $paths ),
            count( $urlAliases )
        );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                $paths[$index],
                $urlAlias->path
            );
        }
    }

    public function providerForTestListAutogeneratedLocationAliasesMultipleLanguagesEmpty()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "/jedan",
                                "eng-GB" => "/jedan",
                            )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-GB" => "dva",
                                "ger-DE" => "dva",
                            )
                        )
                    ),
                    "languageCodes" => array( "eng-GB", "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                array( "cro-HR" ),
            ),
            array(
                $spiUrlAliases,
                array( "ger-DE" ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesMultipleLanguagesEmpty
     */
    public function testListAutogeneratedLocationAliasesMultipleLanguagesEmpty( $spiUrlAliases, $prioritizedLanguageCodes )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, null );

        self::assertEmpty( $urlAliases );
    }

    public function providerForTestListAutogeneratedLocationAliasesWithLanguageCodeMultipleLanguagesPath()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "jedan",
                            )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-GB" => "dva",
                                "ger-DE" => "dva",
                            )
                        )
                    ),
                    "languageCodes" => array( "eng-GB", "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                "ger-DE",
                array( "cro-HR", "ger-DE" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases,
                "ger-DE",
                array( "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases,
                "eng-GB",
                array( "eng-GB" ),
                array(
                    "/jedan/dva",
                ),
            ),
            array(
                $spiUrlAliases,
                "eng-GB",
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "/jedan/dva",
                ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesWithLanguageCodeMultipleLanguagesPath
     */
    public function testListAutogeneratedLocationAliasesWithLanguageCodeMultipleLanguagesPath(
        $spiUrlAliases,
        $languageCode,
        $prioritizedLanguageCodes,
        $paths
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, $languageCode );

        self::assertEquals(
            count( $paths ),
            count( $urlAliases )
        );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                $paths[$index],
                $urlAlias->path
            );
        }
    }

    public function providerForTestListAutogeneratedLocationAliasesWithLanguageCodeMultipleLanguagesEmpty()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "/jedan",
                                "eng-GB" => "/jedan",
                            )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "eng-GB" => "dva",
                                "ger-DE" => "dva",
                            )
                        )
                    ),
                    "languageCodes" => array( "eng-GB", "ger-DE" ),
                    "alwaysAvailable" => false,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                "cro-HR",
                array( "cro-HR" ),
            ),
            array(
                $spiUrlAliases,
                "cro-HR",
                array( "cro-HR", "eng-GB" ),
            ),
            array(
                $spiUrlAliases,
                "cro-HR",
                array( "ger-DE" ),
            ),
            array(
                $spiUrlAliases,
                "cro-HR",
                array( "cro-HR", "eng-GB", "ger-DE" ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesWithLanguageCodeMultipleLanguagesEmpty
     */
    public function testListAutogeneratedLocationAliasesWithLanguageCodeMultipleLanguagesEmpty(
        $spiUrlAliases,
        $languageCode,
        $prioritizedLanguageCodes
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, $languageCode );

        self::assertEmpty( $urlAliases );
    }

    public function providerForTestListAutogeneratedLocationAliasesAlwaysAvailablePath()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "one",
                            )
                        ),
                        array(
                            "always-available" => true,
                            "translations" => array(
                                "ger-DE" => "zwei",
                            )
                        )
                    ),
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => true,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                array( "cro-HR", "ger-DE" ),
                array(
                    "/jedan/zwei",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "ger-DE", "cro-HR" ),
                array(
                    "/jedan/zwei",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "eng-GB" ),
                array(
                    "/one/zwei",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array(
                    "/jedan/zwei",
                ),
            ),
            array(
                $spiUrlAliases,
                array( "eng-GB", "ger-DE", "cro-HR" ),
                array(
                    "/one/zwei",
                ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesAlwaysAvailablePath
     */
    public function testListAutogeneratedLocationAliasesAlwaysAvailablePath(
        $spiUrlAliases,
        $prioritizedLanguageCodes,
        $paths
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, null );

        self::assertEquals(
            count( $paths ),
            count( $urlAliases )
        );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                $paths[$index],
                $urlAlias->path
            );
        }
    }

    public function providerForTestListAutogeneratedLocationAliasesWithLanguageCodeAlwaysAvailablePath()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "one",
                            )
                        ),
                        array(
                            "always-available" => true,
                            "translations" => array(
                                "ger-DE" => "zwei",
                            )
                        )
                    ),
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => true,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                "ger-DE",
                array( "cro-HR", "ger-DE" ),
                array(
                    "/jedan/zwei",
                ),
            ),
            array(
                $spiUrlAliases,
                "ger-DE",
                array( "ger-DE", "cro-HR" ),
                array(
                    "/jedan/zwei",
                ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesWithLanguageCodeAlwaysAvailablePath
     */
    public function testListAutogeneratedLocationAliasesWithLanguageCodeAlwaysAvailablePath(
        $spiUrlAliases,
        $languageCode,
        $prioritizedLanguageCodes,
        $paths
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, $languageCode );

        self::assertEquals(
            count( $paths ),
            count( $urlAliases )
        );

        foreach ( $urlAliases as $index => $urlAlias )
        {
            self::assertEquals(
                $paths[$index],
                $urlAlias->path
            );
        }
    }

    public function providerForTestListAutogeneratedLocationAliasesWithLanguageCodeAlwaysAvailableEmpty()
    {
        $spiUrlAliases = array(
            new SPIUrlAlias(
                array(
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "one",
                            )
                        ),
                        array(
                            "always-available" => true,
                            "translations" => array(
                                "ger-DE" => "zwei",
                            )
                        )
                    ),
                    "languageCodes" => array( "ger-DE" ),
                    "alwaysAvailable" => true,
                )
            ),
        );

        return array(
            array(
                $spiUrlAliases,
                "eng-GB",
                array( "eng-GB" ),
            ),
            array(
                $spiUrlAliases,
                "eng-GB",
                array( "cro-HR", "eng-GB", "ger-DE" ),
            ),
            array(
                $spiUrlAliases,
                "eng-GB",
                array( "eng-GB", "ger-DE", "cro-HR" ),
            ),
        );
    }

    /**
     * Test for the listLocationAliases() method.
     *
     * @dataProvider providerForTestListAutogeneratedLocationAliasesWithLanguageCodeAlwaysAvailableEmpty
     */
    public function testListAutogeneratedLocationAliasesWithLanguageCodeAlwaysAvailableEmpty(
        $spiUrlAliases,
        $languageCode,
        $prioritizedLanguageCodes
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAliases = $urlAliasService->listLocationAliases( $location, false, $languageCode );

        self::assertEmpty( $urlAliases );
    }

    /**
     * Test for the listGlobalAliases() method.
     */
    public function testListGlobalAliases()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "ger-DE" ),
            "showAllTranslations" => true,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listGlobalURLAliases"
        )->with(
            $this->equalTo( null ),
            $this->equalTo( 0 ),
            $this->equalTo( -1 )
        )->will(
            $this->returnValue(
                array(
                    new SPIUrlAlias(
                        array(
                            "pathData" => array(
                                array(
                                    "always-available" => true,
                                    "translations" => array(
                                        "ger-DE" => "squirrel",
                                    )
                                ),
                            ),
                            "languageCodes" => array( "ger-DE" ),
                            "alwaysAvailable" => true,
                        )
                    )
                )
            )
        );

        $urlAliases = $urlAliasService->listGlobalAliases();

        self::assertCount( 1, $urlAliases );
        self::assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias",
            $urlAliases[0]
        );
    }

    /**
     * Test for the listGlobalAliases() method.
     */
    public function testListGlobalAliasesEmpty()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "eng-GB" ),
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listGlobalURLAliases"
        )->with(
            $this->equalTo( null ),
            $this->equalTo( 0 ),
            $this->equalTo( -1 )
        )->will(
            $this->returnValue(
                array(
                    new SPIUrlAlias(
                        array(
                            "pathData" => array(
                                array(
                                    "always-available" => false,
                                    "translations" => array(
                                        "ger-DE" => "squirrel",
                                    )
                                ),
                            ),
                            "languageCodes" => array( "ger-DE" ),
                            "alwaysAvailable" => false,
                        )
                    )
                )
            )
        );

        $urlAliases = $urlAliasService->listGlobalAliases();

        self::assertCount( 0, $urlAliases );
    }

    /**
     * Test for the listGlobalAliases() method.
     */
    public function testListGlobalAliasesWithParameters()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listGlobalURLAliases"
        )->with(
            $this->equalTo( "languageCode" ),
            $this->equalTo( "offset" ),
            $this->equalTo( "limit" )
        )->will(
            $this->returnValue( array() )
        );

        $urlAliases = $urlAliasService->listGlobalAliases( "languageCode", "offset", "limit" );

        self::assertEmpty( $urlAliases );
    }

    /**
     * Test for the lookup() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookupThrowsNotFoundException()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "lookup"
        )->with(
            $this->equalTo( "url" )
        )->will(
            $this->throwException( new NotFoundException( "UrlAlias", "url" ) )
        );

        $urlAliasService->lookup( "url" );
    }

    public function providerForTestLookupThrowsNotFoundExceptionPath()
    {
        return array(
            // alias does not exist in requested language
            array( "ein/dva", array( "cro-HR", "ger-DE" ), "ger-DE" ),
            // alias exists in requested language but the language is not in prioritized languages list
            array( "ein/dva", array( "ger-DE" ), "eng-GB" ),
            // alias path is not matched
            array( "jedan/dva", array( "cro-HR", "ger-DE" ), "cro-HR" ),
            // path is not loadable for prioritized languages list
            array( "ein/dva", array( "cro-HR" ), "cro-HR" ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @dataProvider providerForTestLookupThrowsNotFoundExceptionPath
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLookupThrowsNotFoundExceptionPathNotMatchedOrNotLoadable( $url, $prioritizedLanguageList, $languageCode )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageList,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "lookup"
        )->with(
            $this->equalTo( $url )
        )->will(
            $this->returnValue(
                new SPIUrlAlias(
                    array(
                        "pathData" => array(
                            array(
                                "always-available" => false,
                                "translations" => array( "ger-DE" => "ein" )
                            ),
                            array(
                                "always-available" => false,
                                "translations" => array(
                                    "cro-HR" => "dva",
                                    "eng-GB" => "two",
                                )
                            )
                        ),
                        "languageCodes" => array( "eng-GB", "cro-HR" ),
                        "alwaysAvailable" => false,
                    )
                )
            )
        );

        $urlAliasService->lookup( $url, $languageCode );
    }

    public function providerForTestLookup()
    {
        return array(
            // showAllTranslations setting is true
            array( array( "ger-DE" ), true, false, null ),
            // alias is always available
            array( array( "ger-DE" ), false, true, null ),
            // works with available language code
            array( array( "cro-HR" ), false, false, "eng-GB" ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @dataProvider providerForTestLookup
     */
    public function testLookup( $prioritizedLanguageList, $showAllTranslations, $alwaysAvailable, $languageCode )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageList,
            "showAllTranslations" => $showAllTranslations,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "lookup"
        )->with(
            $this->equalTo( "jedan/dva" )
        )->will(
            $this->returnValue(
                new SPIUrlAlias(
                    array(
                        "pathData" => array(
                            array(
                                "always-available" => $alwaysAvailable,
                                "translations" => array( "cro-HR" => "jedan" )
                            ),
                            array(
                                "always-available" => $alwaysAvailable,
                                "translations" => array(
                                    "cro-HR" => "dva",
                                    "eng-GB" => "two",
                                )
                            )
                        ),
                        "languageCodes" => array( "eng-GB", "cro-HR" ),
                        "alwaysAvailable" => $alwaysAvailable,
                    )
                )
            )
        );

        $urlAlias = $urlAliasService->lookup( "jedan/dva", $languageCode );

        self::assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias",
            $urlAlias
        );
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testReverseLookupThrowsNotFoundException()
    {
        $mockedService = $this->getPartlyMockedURLAliasServiceService( array( "listLocationAliases" ) );
        $configuration = array(
            "prioritizedLanguageList" => array( "ger-DE" ),
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $mockedService, $configuration );

        $languageCode = "eng-GB";
        $location = $this->getLocationStub();

        $mockedService->expects(
            $this->once()
        )->method(
            "listLocationAliases"
        )->with(
            $this->equalTo( $location ),
            $this->equalTo( false ),
            $this->equalTo( $languageCode )
        )->will(
            $this->returnValue(
                array(
                    new UrlAlias(
                        array(
                            "languageCodes" => array( "eng-GB" ),
                            "alwaysAvailable" => false,
                        )
                    )
                )
            )
        );

        $mockedService->reverseLookup( $location, $languageCode );
    }

    public function providerForTestReverseLookup()
    {
        return $this->providerForTestListAutogeneratedLocationAliasesPath();
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @dataProvider providerForTestReverseLookup
     */
    public function testReverseLookupPath( $spiUrlAliases, $prioritizedLanguageCodes, $paths, $reverseLookupLanguageCode )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAlias = $urlAliasService->reverseLookup( $location );

        self::assertEquals(
            array( $reverseLookupLanguageCode ),
            $urlAlias->languageCodes
        );
        self::assertEquals(
            $paths[$reverseLookupLanguageCode],
            $urlAlias->path
        );
    }

    public function providerForTestReverseLookupAlwaysAvailablePath()
    {
        return $this->providerForTestListAutogeneratedLocationAliasesAlwaysAvailablePath();
    }

    /**
     * Test for the reverseLookup() method.
     *
     * @dataProvider providerForTestReverseLookupAlwaysAvailablePath
     */
    public function testReverseLookupAlwaysAvailablePath(
        $spiUrlAliases,
        $prioritizedLanguageCodes,
        $paths
    )
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => $prioritizedLanguageCodes,
            "showAllTranslations" => false,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( $spiUrlAliases )
        );

        $location = $this->getLocationStub();
        $urlAlias = $urlAliasService->reverseLookup( $location );

        self::assertEquals(
            reset( $paths ),
            $urlAlias->path
        );
    }

    /**
     * Test for the reverseLookup() method.
     */
    public function testReverseLookupWithShowAllTranslations()
    {
        $spiUrlAlias = $this->getSpiUrlAlias();
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $configuration = array(
            "prioritizedLanguageList" => array( "fre-FR" ),
            "showAllTranslations" => true,
        );
        $this->setConfiguration( $urlAliasService, $configuration );
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );
        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "listURLAliasesForLocation"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( false )
        )->will(
            $this->returnValue( array( $spiUrlAlias ) )
        );

        $location = $this->getLocationStub();
        $urlAlias = $urlAliasService->reverseLookup( $location );

        self::assertEquals( "/jedan/dva/tri", $urlAlias->path );
    }

    /**
     * Test for the createUrlAlias() method.
     */
    public function testCreateUrlAlias()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();
        $location = $this->getLocationStub();

        $repositoryMock
            ->expects( $this->once() )
            ->method( "beginTransaction" );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "commit" );

        $urlAliasHandlerMock->expects(
            $this->once()
        )->method(
            "createCustomUrlAlias"
        )->with(
            $this->equalTo( $location->id ),
            $this->equalTo( "path" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "alwaysAvailable" )
        )->will(
            $this->returnValue( new SPIUrlAlias )
        );

        $urlAlias = $mockedService->createUrlAlias(
            $location,
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );

        self::assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias",
            $urlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testCreateUrlAliasWithRollback()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();
        $location = $this->getLocationStub();

        $repositoryMock
            ->expects( $this->once() )
            ->method( "beginTransaction" );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "rollback" );

        $urlAliasHandlerMock->expects(
            $this->once()
        )->method(
            "createCustomUrlAlias"
        )->with(
            $this->equalTo( $location->id ),
            $this->equalTo( "path" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "alwaysAvailable" )
        )->will(
            $this->throwException( new Exception( "Handler threw an exception" ) )
        );

        $mockedService->createUrlAlias(
            $location,
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateUrlAliasThrowsInvalidArgumentException()
    {
        $location = $this->getLocationStub();
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "createCustomUrlAlias"
        )->with(
            $this->equalTo( $location->id ),
            $this->equalTo( "path" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "alwaysAvailable" )
        )->will(
            $this->throwException( new ForbiddenException )
        );

        $urlAliasService->createUrlAlias(
            $location,
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     */
    public function testCreateGlobalUrlAlias()
    {
        $resource = "module:content/search";
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();

        $repositoryMock
            ->expects( $this->once() )
            ->method( "beginTransaction" );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "commit" );

        $urlAliasHandlerMock->expects(
            $this->once()
        )->method(
            "createGlobalUrlAlias"
        )->with(
            $this->equalTo( $resource ),
            $this->equalTo( "path" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "alwaysAvailable" )
        )->will(
            $this->returnValue( new SPIUrlAlias )
        );

        $urlAlias = $mockedService->createGlobalUrlAlias(
            $resource,
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );

        self::assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\URLAlias",
            $urlAlias
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testCreateGlobalUrlAliasWithRollback()
    {
        $resource = "module:content/search";
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();

        $repositoryMock
            ->expects( $this->once() )
            ->method( "beginTransaction" );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "rollback" );

        $urlAliasHandlerMock->expects(
            $this->once()
        )->method(
            "createGlobalUrlAlias"
        )->with(
            $this->equalTo( $resource ),
            $this->equalTo( "path" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "alwaysAvailable" )
        )->will(
            $this->throwException( new Exception( "Handler threw an exception" ) )
        );

        $mockedService->createGlobalUrlAlias(
            $resource,
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateGlobalUrlAliasThrowsInvalidArgumentExceptionResource()
    {
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $urlAliasService->createGlobalUrlAlias(
            "invalid/resource",
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateGlobalUrlAliasThrowsInvalidArgumentExceptionPath()
    {
        $resource = "module:content/search";
        $urlAliasService = $this->getRepository()->getURLAliasService();
        $urlAliasHandler = $this->getPersistenceMockHandler( 'Content\\UrlAlias\\Handler' );

        $urlAliasHandler->expects(
            $this->once()
        )->method(
            "createGlobalUrlAlias"
        )->with(
            $this->equalTo( $resource ),
            $this->equalTo( "path" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "alwaysAvailable" )
        )->will(
            $this->throwException( new ForbiddenException )
        );

        $urlAliasService->createGlobalUrlAlias(
            $resource,
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\UrlAliasTest::testCreateUrlAlias
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\UrlAliasTest::testCreateUrlAliasWithRollback
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\UrlAliasTest::testCreateUrlAliasThrowsInvalidArgumentException
     */
    public function testCreateGlobalUrlAliasForLocation()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedURLAliasServiceService( array( "createUrlAlias" ) );
        $location = $this->getLocationStub();
        $locationServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\DomainLogic\\LocationService",
            array(), array(), "", false
        );

        $locationServiceMock->expects(
            $this->exactly( 2 )
        )->method(
            "loadLocation"
        )->with(
            $this->equalTo( 42 )
        )->will(
            $this->returnValue( $location )
        );

        $repositoryMock->expects(
            $this->exactly( 2 )
        )->method(
            "getLocationService"
        )->will(
            $this->returnValue( $locationServiceMock )
        );

        $mockedService->expects(
            $this->exactly( 2 )
        )->method(
            "createUrlAlias"
        )->with(
            $this->equalTo( $location ),
            $this->equalTo( "path" ),
            $this->equalTo( "languageCode" ),
            $this->equalTo( "forwarding" ),
            $this->equalTo( "alwaysAvailable" )
        );

        $mockedService->createGlobalUrlAlias(
            "eznode:42",
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
        $mockedService->createGlobalUrlAlias(
            "module:content/view/full/42",
            "path",
            "languageCode",
            "forwarding",
            "alwaysAvailable"
        );
    }

    /**
     * @param int $id
     *
     * @return \eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location
     */
    protected function getLocationStub( $id = 42 )
    {
        return new Location( array( "id" => $id ) );
    }

    /**
     * @param object $urlAliasService
     * @param array $configuration
     */
    protected function setConfiguration( $urlAliasService, array $configuration )
    {
        $refObject = new \ReflectionObject( $urlAliasService );
        $refProperty = $refObject->getProperty( 'settings' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $urlAliasService,
            $configuration
        );
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\DomainLogic\URLAliasService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedURLAliasServiceService( array $methods = null )
    {
        $languageServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\DomainLogic\\LanguageService",
            array(), array(), "", false
        );
        $languageServiceMock->expects(
            $this->once()
        )->method(
            "getPrioritizedLanguageCodeList"
        )->will(
            $this->returnValue( array( "eng-GB" ) )
        );

        $this->getRepositoryMock()->expects(
            $this->once()
        )->method(
            "getContentLanguageService"
        )->will(
            $this->returnValue( $languageServiceMock )
        );

        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\DomainLogic\\URLAliasService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->urlAliasHandler()
            )
        );
    }
}
