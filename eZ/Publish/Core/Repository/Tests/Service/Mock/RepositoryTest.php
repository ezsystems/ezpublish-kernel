<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\RepositoryTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Mock test case for Repository
 */
class RepositoryTest extends BaseServiceMockTest
{
    public function providerForTestHasAccessReturnsTrue()
    {
        return array(
            array(
                array(
                    25 => $this->createRole(
                        array(
                            array( "dummy-module", "dummy-function", "dummy-limitation" ),
                            array( "dummy-module2", "dummy-function2", "dummy-limitation2" )
                        ),
                        25
                    ),
                    26 => $this->createRole(
                        array(
                            array( "*", "dummy-function", "dummy-limitation" )
                        ),
                        26
                    )
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 25,
                        )
                    ),
                    new RoleAssignment(
                        array(
                            "roleId" => 26,
                        )
                    )
                ),
            ),
            array(
                array(
                    27 => $this->createRole(
                        array(
                            array( "test-module", "*", "dummy-limitation" )
                        ),
                        27
                    )
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 27,
                        )
                    )
                ),
            ),
            array(
                array(
                    28 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "*" )
                        ),
                        28
                    )
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 28,
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsTrue
     */
    public function testHasAccessReturnsTrue( array $roles, array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $mockedRepository = $this->getRepository();

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        foreach ( $roleAssignments as $at => $roleAssignment )
        {
            $userHandlerMock
                ->expects( $this->at( $at + 1 ) )
                ->method( "loadRole" )
                ->with( $roleAssignment->roleId )
                ->will( $this->returnValue( $roles[$roleAssignment->roleId] ) );
        }

        $result = $mockedRepository->hasAccess( "test-module", "test-function" );

        self::assertEquals( true, $result );
    }

    public function providerForTestHasAccessReturnsFalse()
    {
        return array(
            array( array(), array() ),
            array(
                array(
                    29 => $this->createRole(
                        array(
                            array( "dummy-module", "dummy-function", "dummy-limitation" )
                        ),
                        29
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 29,
                        )
                    )
                ),
            ),
            array(
                array(
                    30 => $this->createRole(
                        array(
                            array( "test-module", "dummy-function", "dummy-limitation" )
                        ),
                        30
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 30,
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsFalse
     */
    public function testHasAccessReturnsFalse( array $roles, array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $mockedRepository = $this->getRepository();

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        foreach ( $roleAssignments as $at => $roleAssignment )
        {
            $userHandlerMock
                ->expects( $this->at( $at + 1 ) )
                ->method( "loadRole" )
                ->with( $roleAssignment->roleId )
                ->will( $this->returnValue( $roles[$roleAssignment->roleId] ) );
        }

        $result = $mockedRepository->hasAccess( "test-module", "test-function" );

        self::assertEquals( false, $result );
    }

    /**
     * Test for the sudo() & hasAccess() method.
     *
     * @covers \eZ\Publish\Core\Repository\Repository::sudo
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsFalse
     */
    public function testHasAccessReturnsFalseButSudoSoTrue( array $roles, array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $mockedRepository = $this->getRepository();

        $userHandlerMock
            ->expects( $this->never() )
            ->method( $this->anything() );

        $result = $mockedRepository->sudo(
            function ( $repo )
            {
                return $repo->hasAccess( "test-module", "test-function" );
            }
        );

        self::assertEquals( true, $result );
    }

    /**
     * @return array
     */
    public function providerForTestHasAccessReturnsPermissionSets()
    {
        return array(
            array(
                array(
                    31 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "test-limitation" )
                        ),
                        31
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 31,
                        )
                    )
                ),
            ),
            array(
                array(
                    31 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "test-limitation" )
                        ),
                        31
                    ),
                    32 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "test-limitation2" )
                        ),
                        32
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 31,
                        )
                    ),
                    new RoleAssignment(
                        array(
                            "roleId" => 32,
                        )
                    ),
                ),
            )
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsPermissionSets
     */
    public function testHasAccessReturnsPermissionSets( array $roles, array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $roleServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RoleService",
            array(),
            array(),
            '',
            false
        );
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "getRoleService", "getCurrentUser" ),
            array(
                $this->getPersistenceMock(),
            )
        );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $this->getStubbedUser( 14 ) ) );

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        foreach ( $roleAssignments as $at => $roleAssignment )
        {
            $userHandlerMock
                ->expects( $this->at( $at + 1 ) )
                ->method( "loadRole" )
                ->with( $roleAssignment->roleId )
                ->will( $this->returnValue( $roles[$roleAssignment->roleId] ) );
        }

        $permissionSets = array();
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        $count = 0;
        foreach ( $roleAssignments as $i => $roleAssignment )
        {
            $permissionSet = array( "limitation" => null );
            foreach ( $roles[$roleAssignment->roleId]->policies as $k => $policy )
            {
                $policyName = "policy-" . $i . "-" . $k;
                if ( $policy->limitations === 'notfound' )
                {
                    $return = $this->throwException( new LimitationNotFoundException( "notfound" ) );
                    $this->setExpectedException( 'eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException' );
                }
                else
                {
                    $return = $this->returnValue( $policyName );
                    $permissionSet["policies"][] = $policyName;
                }

                $roleServiceMock
                    ->expects( $this->at( $count++ ) )
                    ->method( "buildDomainPolicyObject" )
                    ->with( $policy )
                    ->will( $return );
            }
            if ( !empty( $permissionSet["policies"] ) )
                $permissionSets[] = $permissionSet;
        }

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertEquals(
            $permissionSets,
            $repositoryMock->hasAccess( "test-module", "test-function" )
        );
    }

    /**
     * @return array
     */
    public function providerForTestHasAccessReturnsException()
    {
        return array(
            array(
                array(
                    31 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "notfound" )
                        ),
                        31
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 31,
                        )
                    )
                ),
            ),
            array(
                array(
                    31 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "test-limitation" )
                        ),
                        31
                    ),
                    32 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "notfound" )
                        ),
                        32
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 31,
                        )
                    ),
                    new RoleAssignment(
                        array(
                            "roleId" => 32,
                        )
                    ),
                ),
            )
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsException
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException
     */
    public function testHasAccessReturnsException( array $roles, array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $roleServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RoleService",
            array(),
            array(),
            '',
            false
        );
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "getRoleService", "getCurrentUser" ),
            array(
                $this->getPersistenceMock(),
            )
        );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $this->getStubbedUser( 14 ) ) );

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        foreach ( $roleAssignments as $at => $roleAssignment )
        {
            $userHandlerMock
                ->expects( $this->at( $at + 1 ) )
                ->method( "loadRole" )
                ->with( $roleAssignment->roleId )
                ->will( $this->returnValue( $roles[$roleAssignment->roleId] ) );
        }

        $permissionSets = array();
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        $count = 0;
        foreach ( $roleAssignments as $i => $roleAssignment )
        {
            $permissionSet = array( "limitation" => null );
            foreach ( $roles[$roleAssignment->roleId]->policies as $k => $policy )
            {
                $policyName = "policy-" . $i . "-" . $k;
                if ( $policy->limitations === 'notfound' )
                {
                    $return = $this->throwException( new LimitationNotFoundException( "notfound" ) );
                }
                else
                {
                    $return = $this->returnValue( $policyName );
                    $permissionSet["policies"][] = $policyName;
                }

                $roleServiceMock
                    ->expects( $this->at( $count++ ) )
                    ->method( "buildDomainPolicyObject" )
                    ->with( $policy )
                    ->will( $return );

                if ( $policy->limitations === 'notfound' )
                {
                    break 2;// no more execution after exception
                }
            }
        }

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        $repositoryMock->hasAccess( "test-module", "test-function" );
    }

    public function providerForTestHasAccessReturnsPermissionSetsWithRoleLimitation()
    {
        return array(
            array(
                array(
                    32 => $this->createRole(
                        array(
                            array( "test-module", "test-function", "test-limitation" )
                        ),
                        32
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 32,
                            "limitationIdentifier" => "test-role-limitation",
                            "values" => array( "test-role-limitation-value" ),
                        )
                    )
                ),
            ),
            array(
                array(
                    33 => $this->createRole( array( array( "*", "*", "*" ) ), 33 ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            "roleId" => 33,
                            "limitationIdentifier" => "test-role-limitation",
                            "values" => array( "test-role-limitation-value" ),
                        )
                    )
                ),
            )
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsPermissionSetsWithRoleLimitation
     */
    public function testHasAccessReturnsPermissionSetsWithRoleLimitation( array $roles, array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "getRoleService", "getCurrentUser", "getLimitationService" ),
            array(
                $this->getPersistenceMock(),
            )
        );
        $roleServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RoleService",
            array( "buildDomainPolicyObject" ),
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMockHandler( "User\\Handler" ),
                $limitationService = $this->getMock(
                    "eZ\\Publish\\Core\\Repository\\LimitationService",
                    array( "getLimitationType" )
                )
            ),
            '',
            false
        );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getLimitationService" )
            ->will( $this->returnValue( $limitationService ) );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $this->getStubbedUser( 14 ) ) );

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        foreach ( $roleAssignments as $at => $roleAssignment )
        {
            $userHandlerMock
                ->expects( $this->at( $at + 1 ) )
                ->method( "loadRole" )
                ->with( $roleAssignment->roleId )
                ->will( $this->returnValue( $roles[$roleAssignment->roleId] ) );
        }

        $permissionSets = array();
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ( $roleAssignments as $i => $roleAssignment )
        {
            $permissionSet = array();
            foreach ( $roles[$roleAssignment->roleId]->policies as $k => $policy )
            {
                $policyName = "policy-{$i}-{$k}";
                $permissionSet["policies"][] = $policyName;
                $roleServiceMock
                    ->expects( $this->at( $k ) )
                    ->method( "buildDomainPolicyObject" )
                    ->with( $policy )
                    ->will( $this->returnValue( $policyName ) );
            }

            $permissionSet["limitation"] = "limitation-{$i}";
            $limitationTypeMock
                ->expects( $this->at( $i ) )
                ->method( "buildValue" )
                ->with( $roleAssignment->values )
                ->will( $this->returnValue( $permissionSet["limitation"] ) );
            $limitationService
                ->expects( $this->any() )
                ->method( "getLimitationType" )
                ->with( $roleAssignment->limitationIdentifier )
                ->will( $this->returnValue( $limitationTypeMock ) );

            $permissionSets[] = $permissionSet;
        }

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertEquals(
            $permissionSets,
            $repositoryMock->hasAccess( "test-module", "test-function" )
        );
    }

    /**
     * Returns Role stub.
     *
     * @param array $policiesData
     * @param mixed $roleId
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    private function createRole( array $policiesData, $roleId = null )
    {
        $policies = array();
        foreach ( $policiesData as $policyData )
        {
            $policies[] = new Policy(
                array(
                    "module" => $policyData[0],
                    "function" => $policyData[1],
                    "limitations" => $policyData[2],
                )
            );
        }

        return new Role(
            array(
                "id" => $roleId,
                "policies" => $policies
            )
        );
    }

    public function providerForTestCanUserSimple()
    {
        return array(
            array( true, true ),
            array( false, false ),
            array( array(), false ),
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution paths with permission sets equaling to boolean value or empty array.
     *
     * @covers \eZ\Publish\API\Repository\Repository::canUser
     * @dataProvider providerForTestCanUserSimple
     */
    public function testCanUserSimple( $permissionSets, $result )
    {
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "hasAccess", "getCurrentUser" ),
            array(
                $this->getPersistenceMock(),
            )
        );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "test-module" ), $this->equalTo( "test-function" ) )
            ->will( $this->returnValue( $permissionSets ) );

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ValueObject" );

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertEquals(
            $result,
            $repositoryMock->canUser( "test-module", "test-function", $valueObject, $valueObject )
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution path with permission set defining no limitations.
     *
     * @covers \eZ\Publish\API\Repository\Repository::canUser
     */
    public function testCanUserWithoutLimitations()
    {
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "hasAccess", "getCurrentUser" ),
            array(
                $this->getPersistenceMock(),
            )
        );

        $policyMock = $this->getMock(
            "eZ\\Publish\\SPI\\Persistence\\User\\Policy",
            array( "getLimitations" ),
            array(),
            '',
            false
        );
        $policyMock
            ->expects( $this->once() )
            ->method( "getLimitations" )
            ->will( $this->returnValue( "*" ) );
        $permissionSets = array(
            array(
                "limitation" => null,
                "policies" => array( $policyMock )
            )
        );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "test-module" ), $this->equalTo( "test-function" ) )
            ->will( $this->returnValue( $permissionSets ) );

        $userMock = $this->getStubbedUser( 14 );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $userMock ) );

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ValueObject" );

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertTrue( $repositoryMock->canUser( "test-module", "test-function", $valueObject, $valueObject ) );
    }

    /**
     * @return array
     */
    private function getPermissionSetsMock()
    {
        $roleLimitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" );
        $roleLimitationMock
            ->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "test-role-limitation-identifier" ) );

        $policyLimitationMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" );
        $policyLimitationMock
            ->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "test-policy-limitation-identifier" ) );

        $policyMock = $this->getMock(
            "eZ\\Publish\\SPI\\Persistence\\User\\Policy",
            array( "getLimitations" ),
            array(),
            '',
            false
        );
        $policyMock
            ->expects( $this->any() )
            ->method( "getLimitations" )
            ->will( $this->returnValue( array( $policyLimitationMock, $policyLimitationMock ) ) );

        $permissionSet = array(
            "limitation" => clone $roleLimitationMock,
            "policies" => array( $policyMock, $policyMock )
        );
        $permissionSets = array( $permissionSet, $permissionSet );

        return $permissionSets;
    }

    /**
     * Provides evaluation results for two permission sets, each with a role limitation and two policies,
     * with two limitations per policy.
     *
     * @return array
     */
    public function providerForTestCanUserComplex()
    {
        return array(
            array(
                array( true, true ),
                array(
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                ),
                true
            ),
            array(
                array( false, false ),
                array(
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                ),
                false
            ),
            array(
                array( false, true ),
                array(
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                ),
                true
            ),
            array(
                array( false, true ),
                array(
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                    array(
                        array( true, false ),
                        array( true, true ),
                    ),
                ),
                true
            ),
            array(
                array( true, false ),
                array(
                    array(
                        array( true, false ),
                        array( false, true ),
                    ),
                    array(
                        array( true, true ),
                        array( true, true ),
                    ),
                ),
                false
            ),
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution paths with permission sets containing limitations.
     *
     * @covers \eZ\Publish\API\Repository\Repository::canUser
     * @dataProvider providerForTestCanUserComplex
     */
    public function testCanUserComplex( array $roleLimitationEvaluations, array $policyLimitationEvaluations, $userCan )
    {
        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\ValueObject" );
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "getCurrentUser", "hasAccess", "getLimitationService" ),
            array(),
            "",
            false
        );
        $roleServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RoleService",
            array(),
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMockHandler( "User\\Handler" ),
                $limitationService = $this->getMock(
                    "eZ\\Publish\\Core\\Repository\\LimitationService",
                    array( "getLimitationType" )
                )
            ),
            "",
            false
        );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getLimitationService" )
            ->will( $this->returnValue( $limitationService ) );

        $permissionSets = $this->getPermissionSetsMock();
        $repositoryMock
            ->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "test-module" ), $this->equalTo( "test-function" ) )
            ->will( $this->returnValue( $permissionSets ) );

        $userMock = $this->getStubbedUser( 14 );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $userMock ) );

        $invocation = 0;
        for ( $i = 0; $i < count( $permissionSets ); $i++ )
        {
            $limitation = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );
            $limitation
                ->expects( $this->once() )
                ->method( "evaluate" )
                ->with( $permissionSets[$i]["limitation"], $userMock, $valueObject, array( $valueObject ) )
                ->will( $this->returnValue( $roleLimitationEvaluations[$i] ) );
            $limitationService
                ->expects( $this->at( $invocation++ ) )
                ->method( "getLimitationType" )
                ->with( "test-role-limitation-identifier" )
                ->will( $this->returnValue( $limitation ) );

            if ( !$roleLimitationEvaluations[$i] )
            {
                continue;
            }

            for ( $j = 0; $j < count( $permissionSets[$i]["policies"] ); $j++ )
            {
                /** @var $policy \eZ\Publish\API\Repository\Values\User\Policy */
                $policy = $permissionSets[$i]["policies"][$j];
                $limitations = $policy->getLimitations();
                for ( $k = 0; $k < count( $limitations ); $k++ )
                {
                    $limitationsPass = true;
                    $limitation = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );
                    $limitation
                        ->expects( $this->once() )
                        ->method( "evaluate" )
                        ->with( $limitations[$k], $userMock, $valueObject, array( $valueObject ) )
                        ->will( $this->returnValue( $policyLimitationEvaluations[$i][$j][$k] ) );
                    $limitationService
                        ->expects( $this->at( $invocation++ ) )
                        ->method( "getLimitationType" )
                        ->with( "test-policy-limitation-identifier" )
                        ->will( $this->returnValue( $limitation ) );

                    if ( !$policyLimitationEvaluations[$i][$j][$k] )
                    {
                        $limitationsPass = false;
                        break;
                    }
                }

                /** @var $limitationsPass */
                if ( $limitationsPass )
                {
                    break 2;
                }
            }
        }

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertEquals(
            $userCan,
            $repositoryMock->canUser( "test-module", "test-function", $valueObject, $valueObject )
        );
    }

    /**
     * Test for the canUser() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::canUser
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserThrowsInvalidArgumentException()
    {
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "hasAccess" ),
            array(),
            "",
            false
        );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "test-module" ), $this->equalTo( "test-function" ) )
            ->will( $this->returnValue( array() ) );

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\ValueObject" );

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        $repositoryMock->canUser( "test-module", "test-function", $valueObject, "This is not a target" );
    }

    /**
     * Test for the setCurrentUser() and getCurrentUser() methods.
     *
     * @covers \eZ\Publish\API\Repository\Repository::setCurrentUser
     * @covers \eZ\Publish\API\Repository\Repository::getCurrentUser
     */
    public function testSetAndGetCurrentUser()
    {
        $mockedRepository = $this->getRepository();
        $user = $this->getStubbedUser( 42 );

        $mockedRepository->setCurrentUser( $user );

        self::assertSame(
            $user,
            $mockedRepository->getCurrentUser()
        );
    }

    /**
     * Test for the getCurrentUser() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::getCurrentUser
     */
    public function testGetCurrentUserReturnsAnonymousUser()
    {
        $userServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\UserService" );
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "getUserService" ),
            array(
                $this->getPersistenceMock(),
                array(
                    'user' => array(
                        'anonymousUserID' => 10
                    ),
                )
            )
        );

        $userServiceMock
            ->expects( $this->once() )
            ->method( "loadUser" )
            ->with( 10 )
            ->will( $this->returnValue( "Anonymous User" ) );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getUserService" )
            ->will( $this->returnValue( $userServiceMock ) );

        /** @var $repositoryMock \eZ\Publish\API\Repository\Repository */
        self::assertEquals(
            "Anonymous User",
            $repositoryMock->getCurrentUser()
        );
    }

    /**
     * Test for the beginTransaction() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::beginTransaction
     */
    public function testBeginTransaction()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "beginTransaction"
        );

        $mockedRepository->beginTransaction();
    }

    /**
     * Test for the commit() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::commit
     */
    public function testCommit()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "commit"
        );

        $mockedRepository->commit();
    }

    /**
     * Test for the commit() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::commit
     * @expectedException \RuntimeException
     */
    public function testCommitThrowsRuntimeException()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "commit"
        )->will(
            $this->throwException( new \Exception() )
        );

        $mockedRepository->commit();
    }

    /**
     * Test for the rollback() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::rollback
     */
    public function testRollback()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "rollback"
        );

        $mockedRepository->rollback();
    }

    /**
     * Test for the rollback() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::rollback
     * @expectedException \RuntimeException
     */
    public function testRollbackThrowsRuntimeException()
    {
        $mockedRepository = $this->getRepository();
        $persistenceHandlerMock = $this->getPersistenceMock();

        $persistenceHandlerMock->expects(
            $this->once()
        )->method(
            "rollback"
        )->will(
            $this->throwException( new \Exception() )
        );

        $mockedRepository->rollback();
    }
}
