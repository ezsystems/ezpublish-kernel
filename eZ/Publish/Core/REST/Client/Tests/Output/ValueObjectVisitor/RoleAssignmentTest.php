<?php
/**
 * File containing a RoleAssignmentTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Client\Values\User\RoleAssignment;
use eZ\Publish\Core\REST\Client\Values\User\Role;
use eZ\Publish\Core\REST\Common;

class RoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the RoleAssignment visitor
     *
     * @todo test with limitations
     *
     * @return \eZ\Publish\Core\REST\Client\Values\User\RoleAssignment
     */
    public function testVisitComplete()
    {
        $visitor   = $this->getRoleAssignmentVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $roleAssignment = new RoleAssignment(
            array(
                'role' => new Role(
                    array(
                        'id' => 42
                    )
                )
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignment
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests if result contains Role element
     *
     * @param string $result
     *
     * @depends testVisitComplete
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
     * Tests if result contains Role element attributes
     *
     * @param string $result
     *
     * @depends testVisitComplete
     */
    public function testResultContainsRoleElementAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Role',
                'attributes' => array(
                    'href' => '/user/roles/42',
                    'media-type' => 'application/vnd.ez.api.Role+xml',
                )
            ),
            $result,
            'Invalid <Role> element attributes.',
            false
        );
    }

    /**
     * Returns the RoleAssignment visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\RoleAssignment
     */
    protected function getRoleAssignmentVisitor()
    {
        return new ValueObjectVisitor\RoleAssignment(
            new Common\UrlHandler\eZPublish()
        );
    }
}
