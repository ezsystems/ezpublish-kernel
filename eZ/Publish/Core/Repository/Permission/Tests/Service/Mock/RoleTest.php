<?php
/**
 * File contains: eZ\Publish\Core\Repository\Permission\Tests\Service\Mock\RoleTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Permission\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Permission\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Mock test case for Role service
 */
class RoleTest extends BaseServiceMockTest
{
    /**
     * Test for the createRole() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::createRole
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateRoleCreateStruct
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitations
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testCreateRoleThrowsLimitationValidationException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRoleByIdentifier" ) );
        /** @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStructMock */
        $roleCreateStructMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\RoleCreateStruct" );
        $policyCreateStructMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        /** @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $policyCreateStructMock->module = "mockModule";
        $policyCreateStructMock->function = "mockFunction";
        $roleCreateStructMock->identifier = "mockIdentifier";
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRoleByIdentifier" )
            ->with( $this->equalTo( "mockIdentifier" ) )
            ->will( $this->throwException( new NotFoundException( "Role", "mockIdentifier" ) ) );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array( 42 ) ) );

        $limitationMock->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "mockIdentifier" ) );

        $settings = array(
            "limitationMap" => array( "mockModule" => array( "mockFunction" => array( "mockIdentifier" => true ) ) ),
            "limitationTypes" => array( "mockIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        /** @var \PHPUnit_Framework_MockObject_MockObject $roleCreateStructMock */
        $roleCreateStructMock->expects( $this->once() )
            ->method( "getPolicies" )
            ->will( $this->returnValue( array( $policyCreateStructMock ) ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $policyCreateStructMock */
        $policyCreateStructMock->expects( $this->once() )
            ->method( "getLimitations" )
            ->will( $this->returnValue( array( $limitationMock ) ) );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "create" )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStructMock */
        $roleServiceMock->createRole( $roleCreateStructMock );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::addPolicy
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitations
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testAddPolicyThrowsLimitationValidationException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $policyCreateStructMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\PolicyCreateStruct" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $policyCreateStructMock->module = "mockModule";
        $policyCreateStructMock->function = "mockFunction";

        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array( 42 ) ) );

        $limitationMock->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "mockIdentifier" ) );

        $settings = array(
            "limitationMap" => array( "mockModule" => array( "mockFunction" => array( "mockIdentifier" => true ) ) ),
            "limitationTypes" => array( "mockIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        /** @var \PHPUnit_Framework_MockObject_MockObject $policyCreateStructMock */
        $policyCreateStructMock->expects( $this->once() )
            ->method( "getLimitations" )
            ->will( $this->returnValue( array( $limitationMock ) ) );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStructMock */
        $roleServiceMock->addPolicy( $roleMock, $policyCreateStructMock );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::updatePolicy
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitations
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testUpdatePolicyThrowsLimitationValidationException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );
        $policyUpdateStructMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\PolicyUpdateStruct" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $policyMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnCallback(
                    function ( $propertyName )
                    {
                        switch ( $propertyName )
                        {
                            case "module":
                                return "mockModule";
                            case "function":
                                return "mockFunction";
                        }
                        return null;
                    }
                )
            );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array( 42 ) ) );

        $limitationMock->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "mockIdentifier" ) );

        $settings = array(
            "limitationMap" => array( "mockModule" => array( "mockFunction" => array( "mockIdentifier" => true ) ) ),
            "limitationTypes" => array( "mockIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        /** @var \PHPUnit_Framework_MockObject_MockObject $policyCreateStructMock */
        $policyUpdateStructMock->expects( $this->once() )
            ->method( "getLimitations" )
            ->will( $this->returnValue( array( $limitationMock ) ) );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStructMock */
        $roleServiceMock->updatePolicy( $policyMock, $policyUpdateStructMock );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUser
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( false ) );

        $roleServiceMock->assignRoleToUser( $roleMock, $userMock, null );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testAssignRoleToUserThrowsLimitationValidationException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array( 42 ) ) );

        $limitationMock->expects( $this->once() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "testIdentifier" ) );

        $settings = array(
            "limitationTypes" => array( "testIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUser( $roleMock, $userMock, $limitationMock );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testAssignRoleToUserThrowsBadStateException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );

        $limitationMock->expects( $this->once() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "testIdentifier" ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUser( $roleMock, $userMock, $limitationMock );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     */
    public function testAssignRoleToUser()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );

        $repository->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );
        $userMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array() ) );

        $limitationMock->expects( $this->exactly( 2 ) )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "testIdentifier" ) );

        $settings = array(
            "limitationTypes" => array( "testIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $userServiceMock->expects( $this->once() )
            ->method( "loadUser" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $userMock ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );
        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );
        $userHandlerMock->expects( $this->once() )
            ->method( "assignRole" )
            ->with(
                $this->equalTo( 24 ),
                $this->equalTo( 42 ),
                $this->equalTo( array( "testIdentifier" => array() ) )
            );
        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUser( $roleMock, $userMock, $limitationMock );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     */
    public function testAssignRoleToUserWithNullLimitation()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );

        $repository->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );
        $userMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $userServiceMock->expects( $this->once() )
            ->method( "loadUser" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $userMock ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );
        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );
        $userHandlerMock->expects( $this->once() )
            ->method( "assignRole" )
            ->with(
                $this->equalTo( 24 ),
                $this->equalTo( 42 ),
                $this->equalTo( null )
            );
        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $roleServiceMock->assignRoleToUser( $roleMock, $userMock, null );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUser
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \Exception
     */
    public function testAssignRoleToUserWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );

        $repository->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );
        $userMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $userServiceMock->expects( $this->once() )
            ->method( "loadUser" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $userMock ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );
        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );
        $userHandlerMock->expects( $this->once() )
            ->method( "assignRole" )
            ->with(
                $this->equalTo( 24 ),
                $this->equalTo( 42 ),
                $this->equalTo( null )
            )->will( $this->throwException( new \Exception ) );
        $repository->expects( $this->once() )->method( "rollback" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\User $userMock */
        $roleServiceMock->assignRoleToUser( $roleMock, $userMock, null );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup" );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userGroupMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( false ) );

        $roleServiceMock->assignRoleToUserGroup( $roleMock, $userGroupMock, null );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     */
    public function testAssignRoleToUserGroupThrowsLimitationValidationException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array( 42 ) ) );

        $limitationMock->expects( $this->once() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "testIdentifier" ) );

        $settings = array(
            "limitationTypes" => array( "testIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userGroupMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUserGroup( $roleMock, $userGroupMock, $limitationMock );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testAssignRoleGroupToUserThrowsBadStateException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $userGroupMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );

        $limitationMock->expects( $this->once() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "testIdentifier" ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userGroupMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUserGroup( $roleMock, $userGroupMock, $limitationMock );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     */
    public function testAssignRoleToUserGroup()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $userGroupMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup" );
        $limitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );

        $repository->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );
        $userGroupMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $limitationTypeMock->expects( $this->once() )
            ->method( "acceptValue" )
            ->with( $this->equalTo( $limitationMock ) );
        $limitationTypeMock->expects( $this->once() )
            ->method( "validate" )
            ->with( $this->equalTo( $limitationMock ) )
            ->will( $this->returnValue( array() ) );

        $limitationMock->expects( $this->exactly( 2 ) )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "testIdentifier" ) );

        $settings = array(
            "limitationTypes" => array( "testIdentifier" => $limitationTypeMock )
        );
        $this->setConfiguration( $roleServiceMock, $settings );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userGroupMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $userServiceMock->expects( $this->once() )
            ->method( "loadUserGroup" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $userGroupMock ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );
        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );
        $userHandlerMock->expects( $this->once() )
            ->method( "assignRole" )
            ->with(
                $this->equalTo( 24 ),
                $this->equalTo( 42 ),
                $this->equalTo( array( "testIdentifier" => array() ) )
            );
        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        /** @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitationMock */
        $roleServiceMock->assignRoleToUserGroup( $roleMock, $userGroupMock, $limitationMock );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     */
    public function testAssignRoleToUserGroupWithNullLimitation()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $userGroupMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup" );
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );

        $repository->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );
        $userGroupMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userGroupMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $userServiceMock->expects( $this->once() )
            ->method( "loadUserGroup" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $userGroupMock ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );
        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );
        $userHandlerMock->expects( $this->once() )
            ->method( "assignRole" )
            ->with(
                $this->equalTo( 24 ),
                $this->equalTo( 42 ),
                $this->equalTo( null )
            );
        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $roleServiceMock->assignRoleToUserGroup( $roleMock, $userGroupMock, null );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::assignRoleToUserGroup
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::validateLimitation
     * @expectedException \Exception
     */
    public function testAssignRoleToUserGroupWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $userGroupMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup" );
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );

        $repository->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );
        $userGroupMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "assign" ),
                $this->equalTo( $userGroupMock ),
                $this->equalTo( $roleMock )
            )->will( $this->returnValue( true ) );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( $this->equalTo( 42 ) )
            ->will( $this->returnValue( $roleMock ) );

        $userServiceMock->expects( $this->once() )
            ->method( "loadUserGroup" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $userGroupMock ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );
        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );
        $userHandlerMock->expects( $this->once() )
            ->method( "assignRole" )
            ->with(
                $this->equalTo( 24 ),
                $this->equalTo( 42 ),
                $this->equalTo( null )
            )->will( $this->throwException( new \Exception ) );
        $repository->expects( $this->once() )->method( "rollback" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\UserGroup $userGroupMock */
        $roleServiceMock->assignRoleToUserGroup( $roleMock, $userGroupMock, null );
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::deletePolicy
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeletePolicyThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( false ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy( $policyMock );
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::deletePolicy
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testDeletePolicyWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );

        $policyMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "id", 42 ),
                    )
                )
            );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );

        $userHandlerMock->expects( $this->once() )
            ->method( "deletePolicy" )
            ->with(
                $this->equalTo( 42 )
            )->will( $this->throwException( new \Exception( "Handler threw an exception" ) ) );

        $repository->expects( $this->once() )->method( "rollback" );

        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy( $policyMock );
    }

    /**
     * Test for the deletePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::deletePolicy
     */
    public function testDeletePolicy()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );

        $policyMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "id", 42 ),
                    )
                )
            );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );

        $userHandlerMock->expects( $this->once() )
            ->method( "deletePolicy" )
            ->with(
                $this->equalTo( 42 )
            );

        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->deletePolicy( $policyMock );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::removePolicy
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemovePolicyThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( false ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->removePolicy( $roleMock, $policyMock );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::removePolicy
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testRemovePolicyThrowsInvalidArgumentException()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $policyMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "id", 42 ),
                        array( "roleId", 24000 )
                    )
                )
            );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->removePolicy( $roleMock, $policyMock );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::removePolicy
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testRemovePolicyWithRollback()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService();
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $policyMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "id", 42 ),
                        array( "roleId", 24 )
                    )
                )
            );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );

        $userHandlerMock->expects( $this->once() )
            ->method( "deletePolicy" )
            ->with(
                $this->equalTo( 42 )
            )->will( $this->throwException( new \Exception( "Handler threw an exception" ) ) );

        $repository->expects( $this->once() )->method( "rollback" );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $roleServiceMock->removePolicy( $roleMock, $policyMock );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @covers \eZ\Publish\Core\Repository\Permission\RoleService::removePolicy
     */
    public function testRemovePolicy()
    {
        $repository = $this->getRepositoryMock();
        $roleServiceMock = $this->getPartlyMockedRoleService( array( "loadRole" ) );
        $policyMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Policy" );
        $roleMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" );

        $roleMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 24 ) );

        $policyMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "id", 42 ),
                        array( "roleId", 24 )
                    )
                )
            );

        $repository->expects( $this->once() )
            ->method( "hasAccess" )
            ->with(
                $this->equalTo( "role" ),
                $this->equalTo( "update" )
            )->will( $this->returnValue( true ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $userHandlerMock = $this->getPersistenceMockHandler( "User\\Handler" );

        $userHandlerMock->expects( $this->once() )
            ->method( "deletePolicy" )
            ->with(
                $this->equalTo( 42 )
            );

        $repository->expects( $this->once() )->method( "commit" );

        $roleServiceMock->expects( $this->once() )
            ->method( "loadRole" )
            ->with( 24 )
            ->will( $this->returnValue( "ROLE" ) );

        /** @var \eZ\Publish\API\Repository\Values\User\Role $roleMock */
        /** @var \eZ\Publish\API\Repository\Values\User\Policy $policyMock */
        $result = $roleServiceMock->removePolicy( $roleMock, $policyMock );

        $this->assertEquals( "ROLE", $result );
    }

    /**
     * @param object $roleService
     * @param array $configuration
     */
    protected function setConfiguration( $roleService, array $configuration )
    {
        $refObject = new \ReflectionObject( $roleService );
        $refProperty = $refObject->getProperty( 'settings' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $roleService,
            $configuration
        );
    }

    /**
     * @var \eZ\Publish\Core\Repository\Permission\RoleService
     */
    protected $partlyMockedRoleService;

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\Permission\RoleService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedRoleService( array $methods = null )
    {
        if ( !isset( $this->partlyMockedRoleService ) )
        {
            $this->partlyMockedRoleService = $this->getMock(
                "eZ\\Publish\\Core\\Repository\\Permission\\RoleService",
                $methods,
                array(
                    $this->getRepositoryMock(),
                    $this->getPersistenceMockHandler( "User\\Handler" ),
                    array()
                )
            );
        }

        return $this->partlyMockedRoleService;
    }
}
