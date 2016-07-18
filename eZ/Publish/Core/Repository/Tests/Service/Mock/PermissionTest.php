<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\PermissionService;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Mock test case for PermissionService.
 */
class PermissionTest extends BaseServiceMockTest
{
    public function providerForTestHasAccessReturnsTrue()
    {
        return array(
            array(
                array(
                    25 => $this->createRole(
                        array(
                            array('dummy-module', 'dummy-function', 'dummy-limitation'),
                            array('dummy-module2', 'dummy-function2', 'dummy-limitation2'),
                        ),
                        25
                    ),
                    26 => $this->createRole(
                        array(
                            array('*', 'dummy-function', 'dummy-limitation'),
                        ),
                        26
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 25,
                        )
                    ),
                    new RoleAssignment(
                        array(
                            'roleId' => 26,
                        )
                    ),
                ),
            ),
            array(
                array(
                    27 => $this->createRole(
                        array(
                            array('test-module', '*', 'dummy-limitation'),
                        ),
                        27
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 27,
                        )
                    ),
                ),
            ),
            array(
                array(
                    28 => $this->createRole(
                        array(
                            array('test-module', 'test-function', '*'),
                        ),
                        28
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 28,
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @dataProvider providerForTestHasAccessReturnsTrue
     */
    public function testHasAccessReturnsTrue(array $roles, array $roleAssignments)
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $userReferenceMock = $this->getUserReferenceMock();
        $mockedService = $this->getPermissionServiceMock(null);

        $userReferenceMock
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(10));

        $userHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with($this->equalTo(10), $this->equalTo(true))
            ->will($this->returnValue($roleAssignments));

        foreach ($roleAssignments as $at => $roleAssignment) {
            $userHandlerMock
                ->expects($this->at($at + 1))
                ->method('loadRole')
                ->with($roleAssignment->roleId)
                ->will($this->returnValue($roles[$roleAssignment->roleId]));
        }

        $result = $mockedService->hasAccess('test-module', 'test-function');

        self::assertEquals(true, $result);
    }

    public function providerForTestHasAccessReturnsFalse()
    {
        return array(
            array(array(), array()),
            array(
                array(
                    29 => $this->createRole(
                        array(
                            array('dummy-module', 'dummy-function', 'dummy-limitation'),
                        ),
                        29
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 29,
                        )
                    ),
                ),
            ),
            array(
                array(
                    30 => $this->createRole(
                        array(
                            array('test-module', 'dummy-function', 'dummy-limitation'),
                        ),
                        30
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 30,
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @dataProvider providerForTestHasAccessReturnsFalse
     */
    public function testHasAccessReturnsFalse(array $roles, array $roleAssignments)
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $userReferenceMock = $this->getUserReferenceMock();
        $service = $this->getPermissionServiceMock(null);

        $userReferenceMock
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(10));

        $userHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with($this->equalTo(10), $this->equalTo(true))
            ->will($this->returnValue($roleAssignments));

        foreach ($roleAssignments as $at => $roleAssignment) {
            $userHandlerMock
                ->expects($this->at($at + 1))
                ->method('loadRole')
                ->with($roleAssignment->roleId)
                ->will($this->returnValue($roles[$roleAssignment->roleId]));
        }

        $result = $service->hasAccess('test-module', 'test-function');

        self::assertEquals(false, $result);
    }

    /**
     * Test for the sudo() & hasAccess() method.
     */
    public function testHasAccessReturnsFalseButSudoSoTrue()
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $service = $this->getPermissionServiceMock(null);
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock
            ->expects($this->any())
            ->method('getPermissionService')
            ->will($this->returnValue($service));

        $userHandlerMock
            ->expects($this->never())
            ->method($this->anything());

        $result = $service->sudo(
            function (Repository $repo) {
                return $repo->hasAccess('test-module', 'test-function');
            },
            $repositoryMock
        );

        self::assertEquals(true, $result);
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
                            array('test-module', 'test-function', 'test-limitation'),
                        ),
                        31
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 31,
                        )
                    ),
                ),
            ),
            array(
                array(
                    31 => $this->createRole(
                        array(
                            array('test-module', 'test-function', 'test-limitation'),
                        ),
                        31
                    ),
                    32 => $this->createRole(
                        array(
                            array('test-module', 'test-function', 'test-limitation2'),
                        ),
                        32
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 31,
                        )
                    ),
                    new RoleAssignment(
                        array(
                            'roleId' => 32,
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @dataProvider providerForTestHasAccessReturnsPermissionSets
     */
    public function testHasAccessReturnsPermissionSets(array $roles, array $roleAssignments)
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $roleDomainMapper = $this->getRoleDomainMapperMock(['buildDomainPolicyObject']);
        $permissionServiceMock = $this->getPermissionServiceMock(['getCurrentUserReference']);

        $permissionServiceMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        $userHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with($this->isType('integer'), $this->equalTo(true))
            ->will($this->returnValue($roleAssignments));

        foreach ($roleAssignments as $at => $roleAssignment) {
            $userHandlerMock
                ->expects($this->at($at + 1))
                ->method('loadRole')
                ->with($roleAssignment->roleId)
                ->will($this->returnValue($roles[$roleAssignment->roleId]));
        }

        $permissionSets = array();
        $count = 0;
        /* @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ($roleAssignments as $i => $roleAssignment) {
            $permissionSet = array('limitation' => null);
            foreach ($roles[$roleAssignment->roleId]->policies as $k => $policy) {
                $policyName = 'policy-' . $i . '-' . $k;
                $return = $this->returnValue($policyName);
                $permissionSet['policies'][] = $policyName;

                $roleDomainMapper
                    ->expects($this->at($count++))
                    ->method('buildDomainPolicyObject')
                    ->with($policy)
                    ->will($return);
            }

            if (!empty($permissionSet['policies'])) {
                $permissionSets[] = $permissionSet;
            }
        }

        /* @var $repositoryMock \eZ\Publish\Core\Repository\Repository */
        self::assertEquals(
            $permissionSets,
            $permissionServiceMock->hasAccess('test-module', 'test-function')
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
                            array('test-module', 'test-function', 'notfound'),
                        ),
                        31
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 31,
                        )
                    ),
                ),
            ),
            array(
                array(
                    31 => $this->createRole(
                        array(
                            array('test-module', 'test-function', 'test-limitation'),
                        ),
                        31
                    ),
                    32 => $this->createRole(
                        array(
                            array('test-module', 'test-function', 'notfound'),
                        ),
                        32
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 31,
                        )
                    ),
                    new RoleAssignment(
                        array(
                            'roleId' => 32,
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @dataProvider providerForTestHasAccessReturnsException
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException
     */
    public function testHasAccessReturnsException(array $roles, array $roleAssignments)
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $roleDomainMapper = $this->getRoleDomainMapperMock();
        $permissionServiceMock = $this->getPermissionServiceMock(['getCurrentUserReference']);

        $permissionServiceMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        $userHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with($this->isType('integer'), $this->equalTo(true))
            ->will($this->returnValue($roleAssignments));

        foreach ($roleAssignments as $at => $roleAssignment) {
            $userHandlerMock
                ->expects($this->at($at + 1))
                ->method('loadRole')
                ->with($roleAssignment->roleId)
                ->will($this->returnValue($roles[$roleAssignment->roleId]));
        }

        $count = 0;
        /* @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ($roleAssignments as $i => $roleAssignment) {
            $permissionSet = array('limitation' => null);
            foreach ($roles[$roleAssignment->roleId]->policies as $k => $policy) {
                $policyName = 'policy-' . $i . '-' . $k;
                if ($policy->limitations === 'notfound') {
                    $return = $this->throwException(new LimitationNotFoundException('notfound'));
                } else {
                    $return = $this->returnValue($policyName);
                    $permissionSet['policies'][] = $policyName;
                }

                $roleDomainMapper
                    ->expects($this->at($count++))
                    ->method('buildDomainPolicyObject')
                    ->with($policy)
                    ->will($return);

                if ($policy->limitations === 'notfound') {
                    break 2;// no more execution after exception
                }
            }
        }

        $permissionServiceMock->hasAccess('test-module', 'test-function');
    }

    public function providerForTestHasAccessReturnsPermissionSetsWithRoleLimitation()
    {
        return array(
            array(
                array(
                    32 => $this->createRole(
                        array(
                            array('test-module', 'test-function', 'test-limitation'),
                        ),
                        32
                    ),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 32,
                            'limitationIdentifier' => 'test-role-limitation',
                            'values' => array('test-role-limitation-value'),
                        )
                    ),
                ),
            ),
            array(
                array(
                    33 => $this->createRole(array(array('*', '*', '*')), 33),
                ),
                array(
                    new RoleAssignment(
                        array(
                            'roleId' => 33,
                            'limitationIdentifier' => 'test-role-limitation',
                            'values' => array('test-role-limitation-value'),
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @dataProvider providerForTestHasAccessReturnsPermissionSetsWithRoleLimitation
     */
    public function testHasAccessReturnsPermissionSetsWithRoleLimitation(array $roles, array $roleAssignments)
    {
        /** @var $userHandlerMock \PHPUnit_Framework_MockObject_MockObject */
        $userHandlerMock = $this->getPersistenceMock()->userHandler();
        $limitationTypeMock = $this->getMock('eZ\\Publish\\SPI\\Limitation\\Type');
        $limitationService = $this->getLimitationServiceMock();
        $roleDomainMapper = $this->getRoleDomainMapperMock();
        $permissionServiceMock = $this->getPermissionServiceMock(['getCurrentUserReference']);

        $permissionServiceMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        $userHandlerMock
            ->expects($this->once())
            ->method('loadRoleAssignmentsByGroupId')
            ->with($this->isType('integer'), $this->equalTo(true))
            ->will($this->returnValue($roleAssignments));

        foreach ($roleAssignments as $at => $roleAssignment) {
            $userHandlerMock
                ->expects($this->at($at + 1))
                ->method('loadRole')
                ->with($roleAssignment->roleId)
                ->will($this->returnValue($roles[$roleAssignment->roleId]));
        }

        $permissionSets = array();
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ($roleAssignments as $i => $roleAssignment) {
            $permissionSet = array();
            foreach ($roles[$roleAssignment->roleId]->policies as $k => $policy) {
                $policyName = "policy-{$i}-{$k}";
                $permissionSet['policies'][] = $policyName;
                $roleDomainMapper
                    ->expects($this->at($k))
                    ->method('buildDomainPolicyObject')
                    ->with($policy)
                    ->will($this->returnValue($policyName));
            }

            $permissionSet['limitation'] = "limitation-{$i}";
            $limitationTypeMock
                ->expects($this->at($i))
                ->method('buildValue')
                ->with($roleAssignment->values)
                ->will($this->returnValue($permissionSet['limitation']));
            $limitationService
                ->expects($this->any())
                ->method('getLimitationType')
                ->with($roleAssignment->limitationIdentifier)
                ->will($this->returnValue($limitationTypeMock));

            $permissionSets[] = $permissionSet;
        }

        self::assertEquals(
            $permissionSets,
            $permissionServiceMock->hasAccess('test-module', 'test-function')
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
    private function createRole(array $policiesData, $roleId = null)
    {
        $policies = array();
        foreach ($policiesData as $policyData) {
            $policies[] = new Policy(
                array(
                    'module' => $policyData[0],
                    'function' => $policyData[1],
                    'limitations' => $policyData[2],
                )
            );
        }

        return new Role(
            array(
                'id' => $roleId,
                'policies' => $policies,
            )
        );
    }

    public function providerForTestCanUserSimple()
    {
        return array(
            array(true, true),
            array(false, false),
            array(array(), false),
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution paths with permission sets equaling to boolean value or empty array.
     *
     * @dataProvider providerForTestCanUserSimple
     */
    public function testCanUserSimple($permissionSets, $result)
    {
        $permissionServiceMock = $this->getPermissionServiceMock(['hasAccess']);

        $permissionServiceMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('test-module'), $this->equalTo('test-function'))
            ->will($this->returnValue($permissionSets));

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ValueObject');

        self::assertEquals(
            $result,
            $permissionServiceMock->canUser('test-module', 'test-function', $valueObject, [$valueObject])
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution path with permission set defining no limitations.
     */
    public function testCanUserWithoutLimitations()
    {
        $permissionServiceMock = $this->getPermissionServiceMock(
            [
                'hasAccess',
                'getCurrentUserReference',
            ]
        );

        $policyMock = $this->getMock(
            'eZ\\Publish\\SPI\\Persistence\\User\\Policy',
            array('getLimitations'),
            array(),
            '',
            false
        );
        $policyMock
            ->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue('*'));
        $permissionSets = array(
            array(
                'limitation' => null,
                'policies' => array($policyMock),
            ),
        );
        $permissionServiceMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('test-module'), $this->equalTo('test-function'))
            ->will($this->returnValue($permissionSets));

        $permissionServiceMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass(
            'eZ\\Publish\\API\\Repository\\Values\\ValueObject'
        );

        self::assertTrue(
            $permissionServiceMock->canUser(
                'test-module',
                'test-function',
                $valueObject,
                [$valueObject]
            )
        );
    }

    /**
     * @return array
     */
    private function getPermissionSetsMock()
    {
        $roleLimitationMock = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation');
        $roleLimitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('test-role-limitation-identifier'));

        $policyLimitationMock = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation');
        $policyLimitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('test-policy-limitation-identifier'));

        $policyMock = $this->getMock(
            'eZ\\Publish\\SPI\\Persistence\\User\\Policy',
            array('getLimitations'),
            array(),
            '',
            false
        );
        $policyMock
            ->expects($this->any())
            ->method('getLimitations')
            ->will($this->returnValue(array($policyLimitationMock, $policyLimitationMock)));

        $permissionSet = array(
            'limitation' => clone $roleLimitationMock,
            'policies' => array($policyMock, $policyMock),
        );
        $permissionSets = array($permissionSet, $permissionSet);

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
                array(true, true),
                array(
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                ),
                true,
            ),
            array(
                array(false, false),
                array(
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                ),
                false,
            ),
            array(
                array(false, true),
                array(
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                ),
                true,
            ),
            array(
                array(false, true),
                array(
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                    array(
                        array(true, false),
                        array(true, true),
                    ),
                ),
                true,
            ),
            array(
                array(true, false),
                array(
                    array(
                        array(true, false),
                        array(false, true),
                    ),
                    array(
                        array(true, true),
                        array(true, true),
                    ),
                ),
                false,
            ),
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution paths with permission sets containing limitations.
     *
     * @dataProvider providerForTestCanUserComplex
     */
    public function testCanUserComplex(array $roleLimitationEvaluations, array $policyLimitationEvaluations, $userCan)
    {
        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\ValueObject');
        $limitationServiceMock = $this->getLimitationServiceMock();
        $permissionServiceMock = $this->getPermissionServiceMock(
            [
                'hasAccess',
                'getCurrentUserReference',
            ]
        );

        $permissionSets = $this->getPermissionSetsMock();
        $permissionServiceMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('test-module'), $this->equalTo('test-function'))
            ->will($this->returnValue($permissionSets));

        $userRef = new UserReference(14);
        $permissionServiceMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        $invocation = 0;
        for ($i = 0; $i < count($permissionSets); ++$i) {
            $limitation = $this->getMock('eZ\\Publish\\SPI\\Limitation\\Type');
            $limitation
                ->expects($this->once())
                ->method('evaluate')
                ->with($permissionSets[$i]['limitation'], $userRef, $valueObject, array($valueObject))
                ->will($this->returnValue($roleLimitationEvaluations[$i]));
            $limitationServiceMock
                ->expects($this->at($invocation++))
                ->method('getLimitationType')
                ->with('test-role-limitation-identifier')
                ->will($this->returnValue($limitation));

            if (!$roleLimitationEvaluations[$i]) {
                continue;
            }

            for ($j = 0; $j < count($permissionSets[$i]['policies']); ++$j) {
                /** @var $policy \eZ\Publish\API\Repository\Values\User\Policy */
                $policy = $permissionSets[$i]['policies'][$j];
                $limitations = $policy->getLimitations();
                for ($k = 0; $k < count($limitations); ++$k) {
                    $limitationsPass = true;
                    $limitation = $this->getMock('eZ\\Publish\\SPI\\Limitation\\Type');
                    $limitation
                        ->expects($this->once())
                        ->method('evaluate')
                        ->with($limitations[$k], $userRef, $valueObject, array($valueObject))
                        ->will($this->returnValue($policyLimitationEvaluations[$i][$j][$k]));
                    $limitationServiceMock
                        ->expects($this->at($invocation++))
                        ->method('getLimitationType')
                        ->with('test-policy-limitation-identifier')
                        ->will($this->returnValue($limitation));

                    if (!$policyLimitationEvaluations[$i][$j][$k]) {
                        $limitationsPass = false;
                        break;
                    }
                }

                /** @var $limitationsPass */
                if ($limitationsPass) {
                    break 2;
                }
            }
        }

        self::assertEquals(
            $userCan,
            $permissionServiceMock->canUser(
                'test-module',
                'test-function',
                $valueObject,
                [$valueObject]
            )
        );
    }

    /**
     * Test for the setCurrentUserReference() and getCurrentUserReference() methods.
     */
    public function testSetAndGetCurrentUserReference()
    {
        $permissionServiceMock = $this->getPermissionServiceMock(null);
        $userReferenceMock = $this->getUserReferenceMock();

        $userReferenceMock
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(42));

        $permissionServiceMock->setCurrentUserReference($userReferenceMock);

        self::assertSame(
            $userReferenceMock,
            $permissionServiceMock->getCurrentUserReference()
        );
    }

    /**
     * Test for the getCurrentUserReference() method.
     */
    public function testGetCurrentUserReferenceReturnsAnonymousUser()
    {
        $permissionServiceMock = $this->getPermissionServiceMock(null);
        $userReferenceMock = $this->getUserReferenceMock();

        self::assertSame(
            $userReferenceMock,
            $permissionServiceMock->getCurrentUserReference()
        );
    }

    protected $permissionServiceMock;

    /**
     * @return \eZ\Publish\API\Repository\PermissionService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPermissionServiceMock($methods = [])
    {
        if ($this->permissionServiceMock === null) {
            $this->permissionServiceMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\PermissionService')
                ->setMethods($methods)
                ->setConstructorArgs(
                    [
                        $this->getRoleDomainMapperMock(),
                        $this->getLimitationServiceMock(),
                        $this->getPersistenceMock()->userHandler(),
                        $this->getUserReferenceMock(),
                    ]
                )
                ->getMock();
        }

        return $this->permissionServiceMock;
    }

    protected $userReferenceMock;

    protected function getUserReferenceMock()
    {
        if ($this->userReferenceMock === null) {
            $this->userReferenceMock = $this
                ->getMockBuilder('eZ\Publish\API\Repository\Values\User\UserReference')
                ->getMock();
        }

        return $this->userReferenceMock;
    }

    protected $repositoryMock;

    /**
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock($methods = [])
    {
        if ($this->repositoryMock === null) {
            $this->repositoryMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Repository')
                ->setMethods(['getPermissionService'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->repositoryMock;
    }

    protected $roleDomainMapperMock;

    /**
     * @return \eZ\Publish\Core\Repository\Helper\RoleDomainMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRoleDomainMapperMock($methods = [])
    {
        if ($this->roleDomainMapperMock === null) {
            $this->roleDomainMapperMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\RoleDomainMapper')
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->roleDomainMapperMock;
    }

    protected $limitationServiceMock;

    /**
     * @return \eZ\Publish\Core\Repository\Helper\LimitationService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLimitationServiceMock($methods = [])
    {
        if ($this->limitationServiceMock === null) {
            $this->limitationServiceMock = $this
                ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Helper\\LimitationService')
                ->setMethods($methods)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->limitationServiceMock;
    }
}
