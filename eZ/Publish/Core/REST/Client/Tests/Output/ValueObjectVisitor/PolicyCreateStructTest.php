<?php

/**
 * File containing a PolicyCreateStructTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Client\Values\User;

class PolicyCreateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the PolicyCreateStruct visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeLimitation = new \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation();
        $contentTypeLimitation->limitationValues = array(1, 2, 3);

        $policyCreateStruct = new User\PolicyCreateStruct('content', 'delete');
        $policyCreateStruct->addLimitation($contentTypeLimitation);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policyCreateStruct
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Tests that the result contains PolicyCreate element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyCreateElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'PolicyCreate',
                'children' => array(
                    'less_than' => 4,
                    'greater_than' => 1,
                ),
            ),
            $result,
            'Invalid <PolicyCreate> element.',
            false
        );
    }

    /**
     * Tests that the result contains PolicyCreate attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyCreateAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'PolicyCreate',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.PolicyCreate+xml',
                ),
            ),
            $result,
            'Invalid <PolicyCreate> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains module value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsModuleValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'module',
                'content' => 'content',
            ),
            $result,
            'Invalid or non-existing <PolicyCreate> module value element.',
            false
        );
    }

    /**
     * Tests that the result contains function value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsFunctionValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'function',
                'content' => 'delete',
            ),
            $result,
            'Invalid or non-existing <PolicyCreate> function value element.',
            false
        );
    }

    /**
     * Tests that the result contains limitations element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationsElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'limitations',
            ),
            $result,
            'Invalid or non-existing <PolicyCreate> limitations element.',
            false
        );
    }

    /**
     * Tests that the result contains limitations attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLimitationsAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'limitations',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.limitations+xml',
                ),
            ),
            $result,
            'Invalid <PolicyCreate> limitations attributes.',
            false
        );
    }

    /**
     * Gets the PolicyCreateStruct visitor.
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\PolicyCreateStruct
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\PolicyCreateStruct();
    }
}
