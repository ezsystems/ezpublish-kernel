<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\User;

class RoleTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Role visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $role = new User\Role(
            [
                'id' => 42,
                'identifier' => 'some-role',
                /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
                'mainLanguageCode' => 'eng-GB',
                'names' => array(
                    'eng-GB' => 'Role name EN',
                    'eng-US' => 'Role name EN US',
                ),
                'descriptions' => array(
                    'eng-GB' => 'Role description EN',
                    'eng-US' => 'Role description EN US',
                )
                */
            ]
        );

        $this->addRouteExpectation('ezpublish_rest_loadRole', ['roleId' => $role->id], "/user/roles/{$role->id}");
        $this->addRouteExpectation('ezpublish_rest_loadPolicies', ['roleId' => $role->id], "/user/roles/{$role->id}/policies");

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $role
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Role element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <Role> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRoleAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Role',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Role+xml',
                    'href' => '/user/roles/42',
                ],
            ],
            $result,
            'Invalid <Role> attributes.',
            false
        );
    }

    /**
     * Test if result contains identifier value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'some-role',
            ],
            $result,
            'Invalid or non-existing <Role> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains mainLanguageCode value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsMainLanguageCodeValueElement($result)
    {
        $this->markTestSkipped('@todo uncomment when support for multilingual names and descriptions is added EZP-24776');
        $this->assertXMLTag(
            [
                'tag' => 'mainLanguageCode',
                'content' => 'eng-GB',
            ],
            $result,
            'Invalid or non-existing <Role> mainLanguageCode value element.',
            false
        );
    }

    /**
     * Test if result contains names element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNamesElement($result)
    {
        $this->markTestSkipped('@todo uncomment when support for multilingual names and descriptions is added EZP-24776');
        $this->assertXMLTag(
            [
                'tag' => 'names',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <names> element.',
            false
        );
    }

    /**
     * Test if result contains descriptions element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDescriptionsElement($result)
    {
        $this->markTestSkipped('@todo uncomment when support for multilingual names and descriptions is added EZP-24776');
        $this->assertXMLTag(
            [
                'tag' => 'descriptions',
                'children' => [
                    'count' => 2,
                ],
            ],
            $result,
            'Invalid <descriptions> element.',
            false
        );
    }

    /**
     * Test if result contains Policies element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPoliciesElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Policies',
            ],
            $result,
            'Invalid <Policies> element.',
            false
        );
    }

    /**
     * Test if result contains Policies element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPoliciesAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Policies',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.PolicyList+xml',
                    'href' => '/user/roles/42/policies',
                ],
            ],
            $result,
            'Invalid <Policies> attributes.',
            false
        );
    }

    /**
     * Get the Role visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\Role
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Role();
    }
}
