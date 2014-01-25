<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\DomainLogic\Values\User;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Server\Values;

class RestUserRoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestUserRoleAssignment visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $userRoleAssignment = new Values\RestUserRoleAssignment(
            new User\UserRoleAssignment(
                array(
                    'role' => new User\Role(
                        array(
                            'id'         => 42,
                            'identifier' => 'some-role'
                        )
                    )
                )
            ),
            14
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadRoleAssignmentForUser',
            array(
                'userId' => $userRoleAssignment->id,
                'roleId' => $userRoleAssignment->roleAssignment->role->id
            ),
            "/user/users/{$userRoleAssignment->id}/roles/{$userRoleAssignment->roleAssignment->role->id}"
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadRole',
            array( 'roleId' => $userRoleAssignment->roleAssignment->role->id ),
            "/user/roles/{$userRoleAssignment->roleAssignment->role->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userRoleAssignment
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains RoleAssignment element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleAssignment',
                'children' => array(
                    'count' => 1
                )
            ),
            $result,
            'Invalid <RoleAssignment> element.',
            false
        );
    }

    /**
     * Test if result contains RoleAssignment element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAssignmentAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleAssignment',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleAssignment+xml',
                    'href'       => '/user/users/14/roles/42',
                )
            ),
            $result,
            'Invalid <RoleAssignment> attributes.',
            false
        );
    }

    /**
     * Test if result contains Role element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Role'
            ),
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Role',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Role+xml',
                    'href'       => '/user/roles/42',
                )
            ),
            $result,
            'Invalid <Role> attributes.',
            false
        );
    }

    /**
     * Get the UserRoleAssignment visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestUserRoleAssignment
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestUserRoleAssignment;
    }
}
