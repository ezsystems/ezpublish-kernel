<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\UrlWildcardBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\API\Repository\Values\Content\URLWildcard;
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
