<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class RoleTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Role visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getRoleVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $role = new User\Role(
            array(
                'id'         => 42,
                'identifier' => 'some-role'
            )
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $role
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Role element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsRoleElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Role',
                'children' => array(
                    'count' => 2
                )
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
     * Test if result contains identifier value element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'identifier',
                'content'  => 'some-role'
            ),
            $result,
            'Invalid or non-existing <Role> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains PolicyList element
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsPolicyListElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'PolicyList'
            ),
            $result,
            'Invalid <PolicyList> element.',
            false
        );
    }

    /**
     * Test if result contains PolicyList element attributes
     *
     * @param string $result
     * @depends testVisit
     */
    public function testResultContainsPolicyListAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'PolicyList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.PolicyList+xml',
                    'href'       => '/user/roles/42/policies',
                )
            ),
            $result,
            'Invalid <PolicyList> attributes.',
            false
        );
    }

    /**
     * Get the Role visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Role
     */
    protected function getRoleVisitor()
    {
        return new ValueObjectVisitor\Role(
            new Common\UrlHandler\eZPublish()
        );
    }
}
