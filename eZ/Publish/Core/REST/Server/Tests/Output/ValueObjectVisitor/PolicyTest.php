<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;

class PolicyTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Policy visitor.
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

        $policy = new User\Policy(
            array(
                'id' => 42,
                'roleId' => '84',
                'module' => 'content',
                'function' => 'delete',
                'limitations' => array(
                    'Class' => $contentTypeLimitation,
                ),
            )
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadPolicy',
            array('roleId' => $policy->roleId, 'policyId' => $policy->id),
            "/user/roles/{$policy->roleId}/policies/{$policy->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $policy
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Policy element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Policy',
                'children' => array(
                    'less_than' => 5,
                    'greater_than' => 2,
                ),
            ),
            $result,
            'Invalid <Policy> element.',
            false
        );
    }

    /**
     * Test if result contains Policy element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPolicyAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Policy',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Policy+xml',
                    'href' => '/user/roles/84/policies/42',
                ),
            ),
            $result,
            'Invalid <Policy> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'id',
                'content' => '42',
            ),
            $result,
            'Invalid or non-existing <Policy> id value element.',
            false
        );
    }

    /**
     * Test if result contains module value element.
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
            'Invalid or non-existing <Policy> module value element.',
            false
        );
    }

    /**
     * Test if result contains function value element.
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
            'Invalid or non-existing <Policy> function value element.',
            false
        );
    }

    /**
     * Test if result contains limitations element.
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
            'Invalid <limitations> element.',
            false
        );
    }

    /**
     * Test if result contains limitations attributes.
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
            ),
            $result,
            'Invalid <limitations> attributes.',
            false
        );
    }

    /**
     * Get the Policy visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Policy
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Policy();
    }
}
