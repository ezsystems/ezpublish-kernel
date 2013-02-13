<?php
/**
 * File containing a RoleUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\User;
use eZ\Publish\Core\REST\Common;

class RoleUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the RoleUpdateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getRoleUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $roleUpdateStruct = new User\RoleUpdateStruct();
        $roleUpdateStruct->identifier = 'some-role';

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleUpdateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains RoleInput element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleInputElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleInput',
                'children' => array(
                    'count' => 1
                )
            ),
            $result,
            'Invalid <RoleInput> element.',
            false
        );
    }

    /**
     * Tests that the result contains RoleInput attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleInputAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'RoleInput',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleInput+xml',
                )
            ),
            $result,
            'Invalid <RoleInput> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains identifier value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'identifier',
                'content'  => 'some-role',
            ),
            $result,
            'Invalid or non-existing <RoleInput> identifier value element.',
            false
        );
    }

    /**
     * Gets the RoleUpdateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\RoleUpdateStruct
     */
    protected function getRoleUpdateStructVisitor()
    {
        return new ValueObjectVisitor\RoleUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
