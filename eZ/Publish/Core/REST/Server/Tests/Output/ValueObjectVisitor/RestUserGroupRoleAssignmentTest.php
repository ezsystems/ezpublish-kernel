<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Server\Values;

class RestUserGroupRoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestUserGroupRoleAssignment visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getRestUserGroupRoleAssignmentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $userGroupRoleAssignment = new Values\RestUserGroupRoleAssignment(
            new User\UserGroupRoleAssignment(
                array(
                    'role' => new User\Role(
                        array(
                            'id'         => 42,
                            'identifier' => 'some-role'
                        )
                    )
                )
            ),
            '/1/5/14'
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $userGroupRoleAssignment
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
                    'href'       => '/user/groups/1/5/14/roles/42',
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
     * Get the RestUserGroupRoleAssignment visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestUserGroupRoleAssignment
     */
    protected function getRestUserGroupRoleAssignmentVisitor()
    {
        return new ValueObjectVisitor\RestUserGroupRoleAssignment(
            new Common\UrlHandler\eZPublish()
        );
    }
}
