<?php
/**
 * File containing the RoleServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;

/**
 * Test case for operations in the RoleService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\RoleService
 */
class RoleServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::createRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testCreateRole
     */
    public function testCreateRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::createRole() is not implemented." );
    }

    /**
     * Test for the loadRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRole
     */
    public function testLoadRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::loadRole() is not implemented." );
    }

    /**
     * Test for the loadRoleByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoleByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testLoadRoleByIdentifier
     */
    public function testLoadRoleByIdentifierThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::loadRoleByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadRoles() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::loadRoles()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadRolesThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::loadRoles() is not implemented." );
    }

    /**
     * Test for the updateRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updateRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testUpdateRole
     */
    public function testUpdateRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::updateRole() is not implemented." );
    }

    /**
     * Test for the deleteRole() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::deleteRole()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\RoleServiceTest::testDeleteRole
     */
    public function testDeleteRoleThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::deleteRole() is not implemented." );
    }

    /**
     * Test for the addPolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::addPolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAddPolicyThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::addPolicy() is not implemented." );
    }

    /**
     * Test for the updatePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::updatePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdatePolicyThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::updatePolicy() is not implemented." );
    }

    /**
     * Test for the removePolicy() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::removePolicy()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemovePolicyThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::removePolicy() is not implemented." );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::assignRoleToUserGroup() is not implemented." );
    }

    /**
     * Test for the assignRoleToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUserGroup($role, $userGroup, $roleLimitation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserGroupThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::assignRoleToUserGroup() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignRoleFromUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::unassignRoleFromUserGroup() is not implemented." );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::assignRoleToUser() is not implemented." );
    }

    /**
     * Test for the assignRoleToUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::assignRoleToUser($role, $user, $roleLimitation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignRoleToUserThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::assignRoleToUser() is not implemented." );
    }

    /**
     * Test for the unassignRoleFromUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::unassignRoleFromUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignRoleFromUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::unassignRoleFromUser() is not implemented." );
    }

    /**
     * Test for the getRoleAssignments() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignments()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testGetRoleAssignmentsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::getRoleAssignments() is not implemented." );
    }

    /**
     * Test for the getRoleAssignmentsForUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testGetRoleAssignmentsForUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::getRoleAssignmentsForUser() is not implemented." );
    }

    /**
     * Test for the getRoleAssignmentsForUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\RoleService::getRoleAssignmentsForUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testGetRoleAssignmentsForUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for RoleService::getRoleAssignmentsForUserGroup() is not implemented." );
    }
}
