<?php

/**
 * File containing the RoleIdTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User\Identifier;

use eZ\Publish\Core\MVC\Symfony\Security\User\ContextProvider\RoleContextProvider;
use FOS\HttpCache\UserContext\UserContext;
use PHPUnit_Framework_TestCase;

class RoleContextProviderTest extends PHPUnit_Framework_TestCase
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
            ->setMethods(array('getRoleService', 'getCurrentUser'))
            ->getMock();

        $this->roleServiceMock = $this->getMock('eZ\\Publish\\API\\Repository\\RoleService');

        $this->repositoryMock
            ->expects($this->any())
            ->method('getRoleService')
            ->will($this->returnValue($this->roleServiceMock));
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
            array(
                'limitationValues' => array('/1/2', '/1/2/43'),
            )
        );
        $limitationForRole3 = $this->generateLimitationMock(
            array(
                'limitationValues' => array('foo', 'bar'),
            )
        );
        $returnedRoleAssignments = array(
            $this->generateRoleAssignmentMock(
                array(
                    'role' => $this->generateRoleMock(
                        array(
                            'id' => $roleId1,
                        )
                    ),
                )
            ),
            $this->generateRoleAssignmentMock(
                array(
                    'role' => $this->generateRoleMock(
                        array(
                            'id' => $roleId2,
                        )
                    ),
                    'limitation' => $limitationForRole2,
                )
            ),
            $this->generateRoleAssignmentMock(
                array(
                    'role' => $this->generateRoleMock(
                        array(
                            'id' => $roleId3,
                        )
                    ),
                    'limitation' => $limitationForRole3,
                )
            ),
        );

        $this->roleServiceMock
            ->expects($this->once())
            ->method('getRoleAssignmentsForUser')
            ->with($user, true)
            ->will($this->returnValue($returnedRoleAssignments));

        $this->assertSame(array(), $userContext->getParameters());
        $contextProvider = new RoleContextProvider($this->repositoryMock);
        $contextProvider->updateUserContext($userContext);
        $userContextParams = $userContext->getParameters();
        $this->assertArrayHasKey('roleIdList', $userContextParams);
        $this->assertSame(array($roleId1, $roleId2, $roleId3), $userContextParams['roleIdList']);
        $this->assertArrayHasKey('roleLimitationList', $userContextParams);
        $limitationIdentifierForRole2 = get_class($limitationForRole2);
        $limitationIdentifierForRole3 = get_class($limitationForRole3);
        $this->assertSame(
            array(
                "$roleId2-$limitationIdentifierForRole2" => array('/1/2', '/1/2/43'),
                "$roleId3-$limitationIdentifierForRole3" => array('foo', 'bar'),
            ),
            $userContextParams['roleLimitationList']
        );
    }

    private function generateRoleAssignmentMock(array $properties = array())
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\Core\\Repository\\Values\\User\\UserRoleAssignment')
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }

    private function generateRoleMock(array $properties = array())
    {
        return $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\User\\Role')
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
    }

    private function generateLimitationMock(array $properties = array())
    {
        $limitationMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation')
            ->setConstructorArgs(array($properties))
            ->getMockForAbstractClass();
        $limitationMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(get_class($limitationMock)));

        return $limitationMock;
    }
}
