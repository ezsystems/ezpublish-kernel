<?php

/**
 * File containing a RoleAssignmentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Client\Values\User\RoleAssignment;
use eZ\Publish\Core\REST\Client\Values\User\Role;

class RoleAssignmentTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the RoleAssignment visitor.
     *
     * @todo test with limitations
     *
     * @return \eZ\Publish\Core\REST\Client\Values\User\RoleAssignment
     */
    public function testVisitComplete()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleAssignment = new RoleAssignment(
            array(
                'role' => new Role(
                    array(
                        'id' => 42,
                    )
                ),
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleAssignment
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests if result contains Role element.
     *
     * @param string $result
     *
     * @depends testVisitComplete
     */
    public function testResultContainsRoleElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Role',
            ),
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * Tests if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisitComplete
     */
    public function testResultContainsRoleElementAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Role',
                'attributes' => array(
                    'href' => '/user/roles/42',
                    'media-type' => 'application/vnd.ez.api.Role+xml',
                ),
            ),
            $result,
            'Invalid <Role> element attributes.',
            false
        );
    }

    /**
     * Returns the RoleAssignment visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\RoleAssignment
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RoleAssignment();
    }
}
