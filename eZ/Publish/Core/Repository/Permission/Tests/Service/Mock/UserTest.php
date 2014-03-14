<?php
/**
 * File contains: eZ\Publish\Core\Repository\Permission\Tests\Service\Mock\UserTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Permission\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Permission\Tests\Service\Mock\Base as BaseServiceMockTest;

/**
 * Mock test case for User Service
 */
class UserTest extends BaseServiceMockTest
{
    /**
     * Test for the deleteUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\UserService::deleteUser
     */
    public function testDeleteUser()
    {
        $repository = $this->getRepositoryMock();
        $userService = $this->getPartlyMockedUserService( array( "loadUser" ) );
        $contentService = $this->getMock( "eZ\\Publish\\API\\Repository\\ContentService" );
        $userHandler = $this->getPersistenceMock()->userHandler();

        $user = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $loadedUser = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $versionInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );
        $contentInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );

        $user->expects( $this->once() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $versionInfo->expects( $this->once() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        $loadedUser->expects( $this->once() )
            ->method( "getVersionInfo" )
            ->will( $this->returnValue( $versionInfo ) );

        $loadedUser->expects( $this->once() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $userService->expects( $this->once() )
            ->method( "loadUser" )
            ->with( 42 )
            ->will( $this->returnValue( $loadedUser ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $contentService->expects( $this->once() )
            ->method( "deleteContent" )
            ->with( $contentInfo );

        $repository->expects( $this->once() )
            ->method( "getContentService" )
            ->will( $this->returnValue( $contentService ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $userHandler */
        $userHandler->expects( $this->once() )
            ->method( "delete" )
            ->with( 42 );

        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\User\User $user */
        $userService->deleteUser( $user );
    }

    /**
     * Test for the deleteUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\UserService::deleteUser
     * @expectedException \Exception
     */
    public function testDeleteUserWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $userService = $this->getPartlyMockedUserService( array( "loadUser" ) );
        $contentService = $this->getMock( "eZ\\Publish\\API\\Repository\\ContentService" );

        $user = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $loadedUser = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $versionInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );
        $contentInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );

        $user->expects( $this->once() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $versionInfo->expects( $this->once() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfo ) );

        $loadedUser->expects( $this->once() )
            ->method( "getVersionInfo" )
            ->will( $this->returnValue( $versionInfo ) );

        $userService->expects( $this->once() )
            ->method( "loadUser" )
            ->with( 42 )
            ->will( $this->returnValue( $loadedUser ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $contentService->expects( $this->once() )
            ->method( "deleteContent" )
            ->with( $contentInfo )
            ->will( $this->throwException( new \Exception ) );

        $repository->expects( $this->once() )
            ->method( "getContentService" )
            ->will( $this->returnValue( $contentService ) );

        $repository->expects( $this->once() )->method( "rollback" );

        /** @var \eZ\Publish\API\Repository\Values\User\User $user */
        $userService->deleteUser( $user );
    }

    /**
     * Returns the User service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\Permission\UserService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedUserService( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Permission\\UserService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->userHandler()
            )
        );
    }
}
