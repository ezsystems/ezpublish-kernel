<?php

/**
 * File containing a RoleUpdateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\User;

class RoleUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the RoleUpdateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $roleUpdateStruct = new User\RoleUpdateStruct();
        $roleUpdateStruct->identifier = 'some-role';

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $roleUpdateStruct
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
     * Gets the RoleUpdateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\RoleUpdateStruct
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RoleUpdateStruct();
    }
}
