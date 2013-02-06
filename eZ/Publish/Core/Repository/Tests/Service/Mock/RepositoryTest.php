<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

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
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "dummy-module", "dummy-function", "dummy-limitation" ),
                            array( "dummy-module2", "dummy-function2", "dummy-limitation2" )
                        )
                    ),
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "*", "dummy-function", "dummy-limitation" )
                        )
                    )
                )
            ),
            array(
                array(
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "test-module", "*", "dummy-limitation" )
                        )
                    )
                )
            ),
            array(
                array(
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "test-module", "test-function", "*" )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsTrue
     */
    public function testHasAccessReturnsTrue( array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $mockedRepository = $this->getRepository();

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        $result = $mockedRepository->hasAccess( "test-module", "test-function" );

        self::assertEquals( true, $result );
    }

    public function providerForTestHasAccessReturnsFalse()
    {
        return array(
            array( array() ),
            array(
                array(
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "dummy-module", "dummy-function", "dummy-limitation" )
                        )
                    ),
                )
            ),
            array(
                array(
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "test-module", "dummy-function", "dummy-limitation" )
                        )
                    ),
                )
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsFalse
     */
    public function testHasAccessReturnsFalse( array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $mockedRepository = $this->getRepository();

        $userHandlerMock
            ->expects( $this->once() )
            ->method( "loadRoleAssignmentsByGroupId" )
            ->with( $this->isType( "integer" ), $this->equalTo( true ) )
            ->will( $this->returnValue( $roleAssignments ) );

        $result = $mockedRepository->hasAccess( "test-module", "test-function" );

        self::assertEquals( false, $result );
    }

    public function providerForTestHasAccessReturnsPermissionSets()
    {
        return array(
            array(
                array(
                    $this->createRoleAssignment(
                        null,
                        array(
                            array( "test-module", "test-function", "test-limitation" )
                        )
                    ),
                )
            )
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @covers \eZ\Publish\API\Repository\Repository::hasAccess
     * @dataProvider providerForTestHasAccessReturnsPermissionSets
     */
    public function testHasAccessReturnsPermissionSets( array $roleAssignments )
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
                $this->getIOMock(),
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

        $permissionSets = array();
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ( $roleAssignments as $i => $roleAssignment )
        {
            $permissionSet = array( "limitation" => null );
            foreach ( $roleAssignment->role->policies as $k => $policy )
            {
                $policyName = "policy-" . $i . "-" . $k;
                $roleServiceMock
                    ->expects( $this->at( $k ) )
                    ->method( "buildDomainPolicyObject" )
                    ->with( $policy )
                    ->will( $this->returnValue( $policyName ) );
                $permissionSet["policies"][] = $policyName;
            }
            $permissionSets[] = $permissionSet;
        }

        /** @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertEquals(
            $permissionSets,
            $repositoryMock->hasAccess( "test-module", "test-function" )
        );
    }

    public function providerForTestHasAccessReturnsPermissionSetsWithRoleLimitation()
    {
        return array(
            array(
                array(
                    $this->createRoleAssignment(
                        "test-role-limitation",
                        array(
                            array( "test-module", "test-function", "test-limitation" )
                        ),
                        array( "test-role-limitation-value" )
                    ),
                ),
            ),
            array(
                array(
                    $this->createRoleAssignment(
                        "test-role-limitation",
                        array( array( "*", "*", "*" ) ),
                        array( "test-role-limitation-value" )
                    ),
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
    public function testHasAccessReturnsPermissionSetsWithRoleLimitation( array $roleAssignments )
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );
        $repositoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\Repository",
            array( "getRoleService", "getCurrentUser" ),
            array(
                $this->getPersistenceMock(),
                $this->getIOMock(),
            )
        );
        $roleServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RoleService",
            array( "buildDomainPolicyObject", "getLimitationType" ),
            array(),
            '',
            false
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

        $permissionSets = array();
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ( $roleAssignments as $i => $roleAssignment )
        {
            $permissionSet = array();
            foreach ( $roleAssignment->role->policies as $k => $policy )
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
            $roleServiceMock
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
     * Returns RoleAssignment stub.
     *
     * @param $limitationIdentifier
     * @param array $policiesData
     * @param array $limitationValues
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment
     */
    private function createRoleAssignment( $limitationIdentifier, array $policiesData, array $limitationValues = array() )
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

        return new RoleAssignment(
            array(
                "limitationIdentifier" => $limitationIdentifier,
                "values" => $limitationValues,
                "role" => new Role( array( "policies" => $policies ) )
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
                $this->getIOMock(),
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
                $this->getIOMock(),
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
            array( "getCurrentUser", "getRoleService", "hasAccess" ),
            array(),
            "",
            false
        );
        $roleServiceMock = $this->getMock(
            "eZ\\Publish\\Core\\Repository\\RoleService",
            array( "getLimitationType" ),
            array(),
            "",
            false
        );
        $repositoryMock
            ->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );

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
                ->with( $permissionSets[$i]["limitation"], $userMock, $valueObject, $valueObject )
                ->will( $this->returnValue( $roleLimitationEvaluations[$i] ) );
            $roleServiceMock
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
                        ->with( $limitations[$k], $userMock, $valueObject, $valueObject )
                        ->will( $this->returnValue( $policyLimitationEvaluations[$i][$j][$k] ) );
                    $roleServiceMock
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
                $this->getIOMock(),
            )
        );

        $userServiceMock
            ->expects( $this->once() )
            ->method( "loadAnonymousUser" )
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
