<?php
/**
 * File containing the RoleIdTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\User\Identifier;

use eZ\Publish\Core\MVC\Symfony\Security\User\Identity;
use eZ\Publish\Core\MVC\Symfony\Security\User\IdentityDefiner\Role as RoleDefiner;

class RoleTest extends \PHPUnit_Framework_TestCase
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
            ->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\Repository' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'getRoleService', 'getCurrentUser' ) )
            ->getMock();

        $this->roleServiceMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\RoleService' );

        $this->repositoryMock
            ->expects( $this->any() )
            ->method( 'getRoleService' )
            ->will( $this->returnValue( $this->roleServiceMock ) );
    }

    /**
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\IdentityDefiner\RoleId::__construct
     * @covers eZ\Publish\Core\MVC\Symfony\Security\User\IdentityDefiner\RoleId::setIdentity
     */
    public function testSetIdentity()
    {
        $user = $this->getMock( 'eZ\\Publish\\API\\Repository\\Values\\User\\User' );
        $identity = new Identity();

        $this->repositoryMock
            ->expects( $this->once() )
            ->method( 'getCurrentUser' )
            ->will( $this->returnValue( $user ) );

        $roleId1 = 123;
        $roleId2 = 456;
        $roleId3 = 789;
        $limitationForRole2 = $this->generateLimitationMock(
            array(
                'limitationValues' => array( '/1/2', '/1/2/43' )
            )
        );
        $limitationForRole3 = $this->generateLimitationMock(
            array(
                'limitationValues' => array( 'foo', 'bar' )
            )
        );
        $returnedRoleAssigments = array(
            $this->generateRoleAssignmentMock(
                array(
                    'role' => $this->generateRoleMock(
                        array(
                            'id' => $roleId1
                        )
                    )
                )
            ),
            $this->generateRoleAssignmentMock(
                array(
                    'role' => $this->generateRoleMock(
                        array(
                            'id' => $roleId2
                        )
                    ),
                    'limitation' => $limitationForRole2
                )
            ),
            $this->generateRoleAssignmentMock(
                array(
                    'role' => $this->generateRoleMock(
                        array(
                            'id' => $roleId3
                        )
                    ),
                    'limitation' => $limitationForRole3
                )
            ),
        );

        $this->roleServiceMock
            ->expects( $this->once() )
            ->method( 'getRoleAssignmentsForUser' )
            ->with( $user, true )
            ->will( $this->returnValue( $returnedRoleAssigments ) );

        $this->assertSame( array(), $identity->getInformation() );
        $definer = new RoleDefiner( $this->repositoryMock );
        $definer->setIdentity( $identity );
        $identityInfo = $identity->getInformation();
        $this->assertArrayHasKey( 'roleIdList', $identityInfo );
        $this->assertSame( "$roleId1|$roleId2|$roleId3", $identityInfo['roleIdList'] );
        $this->assertArrayHasKey( 'roleLimitationList', $identityInfo );
        $limitationIdentifierForRole2 = get_class( $limitationForRole2 );
        $limitationIdentifierForRole3 = get_class( $limitationForRole3 );
        $this->assertSame( "$roleId2-$limitationIdentifierForRole2:/1/2|/1/2/43,$roleId3-$limitationIdentifierForRole3:foo|bar", $identityInfo['roleLimitationList'] );
    }

    private function generateRoleAssignmentMock( array $properties = array() )
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\Repository\\Values\\User\\UserRoleAssignment' )
            ->setConstructorArgs( array( $properties ) )
            ->getMockForAbstractClass();
    }

    private function generateRoleMock( array $properties = array() )
    {
        return $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\User\\Role' )
            ->setConstructorArgs( array( $properties ) )
            ->getMockForAbstractClass();
    }

    private function generateLimitationMock( array $properties = array() )
    {
        $limitationMock = $this
            ->getMockBuilder( 'eZ\\Publish\\API\\Repository\\Values\\User\\Limitation\\RoleLimitation' )
            ->setConstructorArgs( array( $properties ) )
            ->getMockForAbstractClass();
        $limitationMock
            ->expects( $this->any() )
            ->method( 'getIdentifier' )
            ->will( $this->returnValue( get_class( $limitationMock ) ) );
        return $limitationMock;
    }
}
