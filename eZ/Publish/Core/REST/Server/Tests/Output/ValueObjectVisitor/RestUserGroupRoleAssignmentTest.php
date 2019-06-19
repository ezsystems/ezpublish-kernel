<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Server\Values;

class RestUserGroupRoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestUserGroupRoleAssignment visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $userGroupRoleAssignment = new Values\RestUserGroupRoleAssignment(
            new User\UserGroupRoleAssignment(
                [
                    'role' => new User\Role(
                        [
                            'id' => 42,
                            'identifier' => 'some-role',
                        ]
                    ),
                ]
            ),
            '/1/5/14'
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadRoleAssignmentForUserGroup',
            [
                'groupPath' => '1/5/14',
                'roleId' => $userGroupRoleAssignment->roleAssignment->role->id,
            ],
            "/user/groups/1/5/14/roles/{$userGroupRoleAssignment->roleAssignment->role->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadRole',
            ['roleId' => $userGroupRoleAssignment->roleAssignment->role->id],
            "/user/roles/{$userGroupRoleAssignment->roleAssignment->role->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupRoleAssignment
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains RoleAssignment element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignment',
                'children' => [
                    'count' => 1,
                ],
            ],
            $result,
            'Invalid <RoleAssignment> element.',
            false
        );
    }

    /**
     * Test if result contains RoleAssignment element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'RoleAssignment',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.RoleAssignment+xml',
                    'href' => '/user/groups/1/5/14/roles/42',
                ],
            ],
            $result,
            'Invalid <RoleAssignment> attributes.',
            false
        );
    }

    /**
     * Test if result contains Role element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
            ],
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Role+xml',
                    'href' => '/user/roles/42',
                ],
            ],
            $result,
            'Invalid <Role> attributes.',
            false
        );
    }

    /**
     * Get the RestUserGroupRoleAssignment visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestUserGroupRoleAssignment
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestUserGroupRoleAssignment();
    }
}
