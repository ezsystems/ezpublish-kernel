<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\UrlWildcardBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
        $service = $this->getRepository()->getURLWildcardService();

        self::assertAttributeSame( $this->getRepository(), "repository", $service );
        self::assertAttributeSame( $this->getPersistenceMockHandler( 'Content\\UrlWildcard\\Handler' ), "urlWildcardHandler", $service );
        self::assertAttributeSame( array(), "settings", $service );
    }

    /**
     * Test for the create() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::create
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateThrowsUnauthorizedException()
    {
        $mockedService = $this->getRepository()->getURLWildcardService();
        $userHandlerMock = $this->getPersistenceMockHandler( 'User\\Handler' );
        $userHandlerMock->expects(
            $this->once()
        )->method(
            "loadRoleAssignmentsByGroupId"
        )->with(
            $this->equalTo( 14 ),
            $this->isTrue()
        )->will(
            $this->returnValue( array() )
        );

        $mockedService->create( "lorem/ipsum", "opossum", true );
    }

    /**
     * Test for the remove() method.
     *
     * @covers \eZ\Publish\Core\Repository\URLWildcardService::remove
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemoveThrowsUnauthorizedException()
    {
        $mockedService = $this->getRepository()->getURLWildcardService();
        $userHandlerMock = $this->getPersistenceMockHandler( 'User\\Handler' );
        $userHandlerMock->expects(
            $this->once()
        )->method(
            "loadRoleAssignmentsByGroupId"
        )->with(
            $this->equalTo( 14 ),
            $this->isTrue()
        )->will(
            $this->returnValue( array() )
        );

        $mockedService->remove( new URLWildcard() );
    }
}
