<?php

/**
 * File containing the RoleIdTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User\Identifier;

use eZ\Publish\Core\MVC\Symfony\Security\User\ContextProvider\RoleContextProvider;
use FOS\HttpCache\UserContext\UserContext;
use PHPUnit\Framework\TestCase;

class RoleContextProviderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $roleServiceMock;

    protected function setUp()
    {
        parent::setUp();
        $this->repositoryMock = $this
            ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['getRoleService', 'getCurrentUser', 'getPermissionResolver'])
            ->getMock();

        $this->roleServiceMock = $this->getMock('eZ\\Publish\\API\\Repository\\RoleService');

        $this->repositoryMock
            ->expects($this->any())
            ->method('getRoleService')
            ->will($this->returnValue($this->roleServiceMock));
        $this->repositoryMock
            ->expects($this->any())
            ->method('getPermissionResolver')
            ->will($this->returnValue($this->getPermissionResolverMock()));
    }

    public function testSetIdentity()
    {
        $user = $this->getMock('eZ\\Publish\\API\\Repository\\Values\\User\\User');
        $userContext = new UserContext();

        $this->repositoryMock
            ->expects($this->once())
            ->method('getCurrentUser')
            ->will($this->returnValue($user));

        $roleId1 = 123;
        $roleId2 = 456;
        $roleId3 = 789;
        $limitationForRole2 = $this->generateLimitationMock(
            [
                'limitationValues' => ['/1/2', '/1/2/43'],
            ]
        );
        $limitationForRole3 = $this->generateLimitationMock(
            [
                'limitationValues' => ['foo', 'bar'],
            ]
        );
        $returnedRoleAssignments = [
            $this->generateRoleAssignmentMock(
                [
                    'role' => $this->generateRoleMock(
                        [
                            'id' => $roleId1,
                        ]
                    ),
                ]
            ),
            $this->generateRoleAssignmentMock(
                [
                    'role' => $this->generateRoleMock(
                        [
                            'id' => $roleId2,
                        ]
                    ),
                    'limitation' => $limitationForRole2,
                ]
            ),
            $this->generateRoleAssignmentMock(
                [
                    'role' => $this->generateRoleMock(
                        [
                            'id' => $roleId3,
                        ]
                    ),
                    'limitation' => $limitationForRole3,
                ]
            ),
        ];

        $this->roleServiceMock
            ->expects($this->once())
            ->method('getRoleAssignmentsForUser')
            ->with($user, true)
            ->will($this->returnValue($returnedRoleAssignments));

        $this->assertSame([], $userContext->getParameters());
        $contextProvider = new RoleContextProvider($this->repositoryMock);
        $contextProvider->updateUserContext($userContext);
        $userContextParams = $userContext->getParameters();
        $this->assertArrayHasKey('roleIdList', $userContextParams);
        $this->assertSame([$roleId1, $roleId2, $roleId3], $userContextParams['roleIdList']);
        $this->assertArrayHasKey('roleLimitationList', $userContextParams);
        $limitationIdentifierForRole2 = get_class($limitationForRole2);
        $limitationIdentifierForRole3 = get_class($limitationForRole3);
        $this->assertSame(
            [
                "$roleId2-$limitationIdentifierForRole2" => ['/1/2', '/1/2/43'],
                "$roleId3-$limitationIdentifierForRole3" => ['foo', 'bar'],
            ],
            $userContextParams['roleLimitationList']
        );
    }

    private function generateRoleAssignmentMock(array $properties = [])
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Values\\User\\UserRoleAssignment')
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    private function generateRoleMock(array $properties = [])
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\User\\Role')
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
    }

    private function generateLimitationMock(array $properties = [])
    {
        $limitationMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation')
            ->setConstructorArgs([$properties])
            ->getMockForAbstractClass();
        $limitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(get_class($limitationMock)));

        return $limitationMock;
    }

    protected function getPermissionResolverMock()
    {
        return $this
            ->getMockBuilder('\eZ\Publish\Core\Repository\Permission\PermissionResolver')
            ->setMethods(null)
            ->setConstructorArgs(
                [
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\RoleDomainMapper')
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\Core\Repository\Helper\LimitationService')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\SPI\Persistence\User\Handler')
                        ->getMock(),
                    $this
                        ->getMockBuilder('eZ\Publish\API\Repository\Values\User\UserReference')
                        ->getMock(),
                ]
            )
            ->getMock();
    }
}
