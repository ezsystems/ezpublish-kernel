<?php

/**
 * File containing a RoleCreateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Client\Values\User;

class RoleCreateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the RoleCreateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleCreateStruct = new User\RoleCreateStruct('some-role');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleCreateStruct
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the result contains RoleInput element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleInputElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'RoleInput',
                'children' => array(
                    'count' => 1,
                ),
            ),
            $result,
            'Invalid <RoleInput> element.',
            false
        );
    }

    /**
     * Tests that the result contains RoleInput attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleInputAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'RoleInput',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RoleInput+xml',
                ),
            ),
            $result,
            'Invalid <RoleInput> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains identifier value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'identifier',
                'content' => 'some-role',
            ),
            $result,
            'Invalid or non-existing <RoleInput> identifier value element.',
            false
        );
    }

    /**
     * Gets the RoleCreateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\RoleCreateStruct
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RoleCreateStruct();
    }
}
