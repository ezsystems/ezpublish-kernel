<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\RoleBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;

/**
 * Test case for Role Service
 *
 */
abstract class RoleBase extends BaseServiceTest
{
    /**
     * Test creating a role with empty name
     * @expectedException eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRoleWithEmptyName()
    {
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( "" );

        $roleService->createRole( $roleCreateStruct );
    }

    /**
     * Test creating a role with existing name
     * @expectedException eZ\Publish\Core\Base\Exceptions\IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRoleWithExistingName()
    {
        self::markTestSkipped( "@todo: enable when RoleService::loadRole implementation is done" );
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( "Anonymous" );

        $roleService->createRole( $roleCreateStruct );
    }

    /**
     * Test creating a role
     * @covers \eZ\Publish\API\Repository\RoleService::createRole
     */
    public function testCreateRole()
    {
        $roleService = $this->repository->getRoleService();
        $roleCreateStruct = $roleService->newRoleCreateStruct( "Ultimate permissions" );

        $createdRole = $roleService->createRole( $roleCreateStruct );

        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Role" , $createdRole );
        self::assertGreaterThan( 0, $createdRole->id );
        self::assertEquals( $roleCreateStruct->name, $createdRole->name );
        self::assertEmpty( $createdRole->getPolicies() );
    }
}
