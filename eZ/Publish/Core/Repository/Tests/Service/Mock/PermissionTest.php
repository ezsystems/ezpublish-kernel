<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Permission\PermissionResolver;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\Core\Base\Exceptions\NotFound\LimitationNotFoundException;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Mock test case for PermissionResolver.
 *
 * @todo Move to "Tests/Permission/"
 */
class PermissionTest extends BaseServiceMockTest
{
    public function providerForTestHasAccessReturnsTrue()
    {
        return [
            [
                [
                    25 => $this->createRole(
                        [
                            ['dummy-module', 'dummy-function', 'dummy-limitation'],
                            ['dummy-module2', 'dummy-function2', 'dummy-limitation2'],
                        ],
                        25
                    ),
                    26 => $this->createRole(
                        [
                            ['*', 'dummy-function', 'dummy-limitation'],
                        ],
                        26
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 25,
                        ]
                    ),
                    new RoleAssignment(
                        [
                            'roleId' => 26,
                        ]
                    ),
                ],
            ],
            [
                [
                    27 => $this->createRole(
                        [
                            ['test-module', '*', 'dummy-limitation'],
                        ],
                        27
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 27,
                        ]
                    ),
                ],
            ],
            [
                [
                    28 => $this->createRole(
                        [
                            ['test-module', 'test-function', '*'],
                        ],
                        28
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 28,
                        ]
                    ),
                ],
            ],
        ];
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
        $mockedService = $this->getPermissionResolverMock(null);

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
        return [
            [[], []],
            [
                [
                    29 => $this->createRole(
                        [
                            ['dummy-module', 'dummy-function', 'dummy-limitation'],
                        ],
                        29
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 29,
                        ]
                    ),
                ],
            ],
            [
                [
                    30 => $this->createRole(
                        [
                            ['test-module', 'dummy-function', 'dummy-limitation'],
                        ],
                        30
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 30,
                        ]
                    ),
                ],
            ],
        ];
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
        $service = $this->getPermissionResolverMock(null);

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
        $service = $this->getPermissionResolverMock(null);
        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock
            ->expects($this->any())
            ->method('getPermissionResolver')
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
        return [
            [
                [
                    31 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'test-limitation'],
                        ],
                        31
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 31,
                        ]
                    ),
                ],
            ],
            [
                [
                    31 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'test-limitation'],
                        ],
                        31
                    ),
                    32 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'test-limitation2'],
                        ],
                        32
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 31,
                        ]
                    ),
                    new RoleAssignment(
                        [
                            'roleId' => 32,
                        ]
                    ),
                ],
            ],
        ];
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
        $permissionResolverMock = $this->getPermissionResolverMock(['getCurrentUserReference']);

        $permissionResolverMock
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

        $permissionSets = [];
        $count = 0;
        /* @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ($roleAssignments as $i => $roleAssignment) {
            $permissionSet = ['limitation' => null];
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
            $permissionResolverMock->hasAccess('test-module', 'test-function')
        );
    }

    /**
     * @return array
     */
    public function providerForTestHasAccessReturnsException()
    {
        return [
            [
                [
                    31 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'notfound'],
                        ],
                        31
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 31,
                        ]
                    ),
                ],
            ],
            [
                [
                    31 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'test-limitation'],
                        ],
                        31
                    ),
                    32 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'notfound'],
                        ],
                        32
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 31,
                        ]
                    ),
                    new RoleAssignment(
                        [
                            'roleId' => 32,
                        ]
                    ),
                ],
            ],
        ];
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
        $permissionResolverMock = $this->getPermissionResolverMock(['getCurrentUserReference']);

        $permissionResolverMock
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
            $permissionSet = ['limitation' => null];
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
                    break 2; // no more execution after exception
                }
            }
        }

        $permissionResolverMock->hasAccess('test-module', 'test-function');
    }

    public function providerForTestHasAccessReturnsPermissionSetsWithRoleLimitation()
    {
        return [
            [
                [
                    32 => $this->createRole(
                        [
                            ['test-module', 'test-function', 'test-limitation'],
                        ],
                        32
                    ),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 32,
                            'limitationIdentifier' => 'test-role-limitation',
                            'values' => ['test-role-limitation-value'],
                        ]
                    ),
                ],
            ],
            [
                [
                    33 => $this->createRole([['*', '*', '*']], 33),
                ],
                [
                    new RoleAssignment(
                        [
                            'roleId' => 33,
                            'limitationIdentifier' => 'test-role-limitation',
                            'values' => ['test-role-limitation-value'],
                        ]
                    ),
                ],
            ],
        ];
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
        $limitationTypeMock = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');
        $limitationService = $this->getLimitationServiceMock();
        $roleDomainMapper = $this->getRoleDomainMapperMock();
        $permissionResolverMock = $this->getPermissionResolverMock(['getCurrentUserReference']);

        $permissionResolverMock
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

        $permissionSets = [];
        /** @var $roleAssignments \eZ\Publish\SPI\Persistence\User\RoleAssignment[] */
        foreach ($roleAssignments as $i => $roleAssignment) {
            $permissionSet = [];
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
            $permissionResolverMock->hasAccess('test-module', 'test-function')
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
        $policies = [];
        foreach ($policiesData as $policyData) {
            $policies[] = new Policy(
                [
                    'module' => $policyData[0],
                    'function' => $policyData[1],
                    'limitations' => $policyData[2],
                ]
            );
        }

        return new Role(
            [
                'id' => $roleId,
                'policies' => $policies,
            ]
        );
    }

    public function providerForTestCanUserSimple()
    {
        return [
            [true, true],
            [false, false],
            [[], false],
        ];
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
        $permissionResolverMock = $this->getPermissionResolverMock(['hasAccess']);

        $permissionResolverMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('test-module'), $this->equalTo('test-function'))
            ->will($this->returnValue($permissionSets));

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\ValueObject');

        self::assertEquals(
            $result,
            $permissionResolverMock->canUser('test-module', 'test-function', $valueObject, [$valueObject])
        );
    }

    /**
     * Test for the canUser() method.
     *
     * Tests execution path with permission set defining no limitations.
     */
    public function testCanUserWithoutLimitations()
    {
        $permissionResolverMock = $this->getPermissionResolverMock(
            [
                'hasAccess',
                'getCurrentUserReference',
            ]
        );

        $policyMock = $this->getMockBuilder('eZ\\Publish\\SPI\\Persistence\\User\\Policy')
            ->setMethods(['getLimitations'])
            ->setConstructorArgs([])
            ->disableOriginalConstructor()
            ->getMock();

        $policyMock
            ->expects($this->once())
            ->method('getLimitations')
            ->will($this->returnValue('*'));
        $permissionSets = [
            [
                'limitation' => null,
                'policies' => [$policyMock],
            ],
        ];
        $permissionResolverMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('test-module'), $this->equalTo('test-function'))
            ->will($this->returnValue($permissionSets));

        $permissionResolverMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        /** @var $valueObject \eZ\Publish\API\Repository\Values\ValueObject */
        $valueObject = $this->getMockForAbstractClass(
            'eZ\\Publish\\API\\Repository\\Values\\ValueObject'
        );

        self::assertTrue(
            $permissionResolverMock->canUser(
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
        $roleLimitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation');
        $roleLimitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('test-role-limitation-identifier'));

        $policyLimitationMock = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation');
        $policyLimitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('test-policy-limitation-identifier'));

        $policyMock = $this->getMockBuilder('eZ\\Publish\\SPI\\Persistence\\User\\Policy')
            ->setMethods(['getLimitations'])
            ->setConstructorArgs([])
            ->getMock();

        $policyMock
            ->expects($this->any())
            ->method('getLimitations')
            ->will($this->returnValue([$policyLimitationMock, $policyLimitationMock]));

        $permissionSet = [
            'limitation' => clone $roleLimitationMock,
            'policies' => [$policyMock, $policyMock],
        ];
        $permissionSets = [$permissionSet, $permissionSet];

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
        return [
            [
                [true, true],
                [
                    [
                        [true, true],
                        [true, true],
                    ],
                    [
                        [true, true],
                        [true, true],
                    ],
                ],
                true,
            ],
            [
                [false, false],
                [
                    [
                        [true, true],
                        [true, true],
                    ],
                    [
                        [true, true],
                        [true, true],
                    ],
                ],
                false,
            ],
            [
                [false, true],
                [
                    [
                        [true, true],
                        [true, true],
                    ],
                    [
                        [true, true],
                        [true, true],
                    ],
                ],
                true,
            ],
            [
                [false, true],
                [
                    [
                        [true, true],
                        [true, true],
                    ],
                    [
                        [true, false],
                        [true, true],
                    ],
                ],
                true,
            ],
            [
                [true, false],
                [
                    [
                        [true, false],
                        [false, true],
                    ],
                    [
                        [true, true],
                        [true, true],
                    ],
                ],
                false,
            ],
        ];
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
        $valueObject = $this->createMock('eZ\\Publish\\API\\Repository\\Values\\ValueObject');
        $limitationServiceMock = $this->getLimitationServiceMock();
        $permissionResolverMock = $this->getPermissionResolverMock(
            [
                'hasAccess',
                'getCurrentUserReference',
            ]
        );

        $permissionSets = $this->getPermissionSetsMock();
        $permissionResolverMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with($this->equalTo('test-module'), $this->equalTo('test-function'))
            ->will($this->returnValue($permissionSets));

        $userRef = new UserReference(14);
        $permissionResolverMock
            ->expects($this->once())
            ->method('getCurrentUserReference')
            ->will($this->returnValue(new UserReference(14)));

        $invocation = 0;
        for ($i = 0; $i < count($permissionSets); ++$i) {
            $limitation = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');
            $limitation
                ->expects($this->once())
                ->method('evaluate')
                ->with($permissionSets[$i]['limitation'], $userRef, $valueObject, [$valueObject])
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
                    $limitation = $this->createMock('eZ\\Publish\\SPI\\Limitation\\Type');
                    $limitation
                        ->expects($this->once())
                        ->method('evaluate')
                        ->with($limitations[$k], $userRef, $valueObject, [$valueObject])
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
            $permissionResolverMock->canUser(
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
        $permissionResolverMock = $this->getPermissionResolverMock(null);
        $userReferenceMock = $this->getUserReferenceMock();

        $userReferenceMock
            ->expects($this->once())
            ->method('getUserId')
            ->will($this->returnValue(42));

        $permissionResolverMock->setCurrentUserReference($userReferenceMock);

        self::assertSame(
            $userReferenceMock,
            $permissionResolverMock->getCurrentUserReference()
        );
    }

    /**
     * Test for the getCurrentUserReference() method.
     */
    public function testGetCurrentUserReferenceReturnsAnonymousUser()
    {
        $permissionResolverMock = $this->getPermissionResolverMock(null);
        $userReferenceMock = $this->getUserReferenceMock();

        self::assertSame(
            $userReferenceMock,
            $permissionResolverMock->getCurrentUserReference()
        );
    }

    protected $permissionResolverMock;

    /**
     * @return \eZ\Publish\API\Repository\PermissionResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPermissionResolverMock($methods = [])
    {
        if ($this->permissionResolverMock === null) {
            $this->permissionResolverMock = $this
                ->getMockBuilder(PermissionResolver::class)
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

        return $this->permissionResolverMock;
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
                ->setMethods(['getPermissionResolver'])
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
