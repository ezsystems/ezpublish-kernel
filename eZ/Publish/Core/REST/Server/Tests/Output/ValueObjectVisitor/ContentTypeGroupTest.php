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
use eZ\Publish\Core\Repository\Values\ContentType;

class ContentTypeGroupTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeGroup visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroup = new ContentType\ContentTypeGroup(
            [
                'id' => 42,
                'identifier' => 'some-group',
                'creationDate' => new \DateTime('2012-12-31 19:30 Europe/Zagreb'),
                'modificationDate' => new \DateTime('2012-12-31 19:35 Europe/Zagreb'),
                'creatorId' => 14,
                'modifierId' => 13,
                /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
                'names' => array(
                    'eng-GB' => 'Group name EN',
                    'eng-US' => 'Group name EN US',
                ),
                'descriptions' => array(
                    'eng-GB' => 'Group description EN',
                    'eng-US' => 'Group description EN US',
                ),
                'mainLanguageCode' => 'eng-GB'
                */
            ]
        );

        $routerMock = $this->getRouterMock();

        $this->addRouteExpectation(
            'ezpublish_rest_loadContentTypeGroup',
            ['contentTypeGroupId' => $contentTypeGroup->id],
            "/content/typegroups/{$contentTypeGroup->id}"
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadUser',
            ['userId' => $contentTypeGroup->creatorId],
            "/user/users/{$contentTypeGroup->creatorId}"
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadUser',
            ['userId' => $contentTypeGroup->modifierId],
            "/user/users/{$contentTypeGroup->modifierId}"
        );

        $this->addRouteExpectation(
            'ezpublish_rest_listContentTypesForGroup',
            ['contentTypeGroupId' => $contentTypeGroup->id],
            "/content/typegroups/{$contentTypeGroup->id}/types"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroup
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ContentTypeGroup element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroup',
                'children' => [
                    'count' => 7,
                ],
            ],
            $result,
            'Invalid <ContentTypeGroup> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypeGroup element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypeGroupAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypeGroup',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentTypeGroup+xml',
                    'href' => '/content/typegroups/42',
                ],
            ],
            $result,
            'Invalid <ContentTypeGroup> attributes.',
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
            [
                'tag' => 'id',
                'content' => '42',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> id value element.',
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
                'content' => 'some-group',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains created value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsCreatedValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'created',
                'content' => '2012-12-31T19:30:00+01:00',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> created value element.',
            false
        );
    }

    /**
     * Test if result contains modified value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsModifiedValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'modified',
                'content' => '2012-12-31T19:35:00+01:00',
            ],
            $result,
            'Invalid or non-existing <ContentTypeGroup> modified value element.',
            false
        );
    }

    /**
     * Test if result contains Creator element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsCreatorElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Creator',
            ],
            $result,
            'Invalid <Creator> element.',
            false
        );
    }

    /**
     * Test if result contains Creator element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsCreatorAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Creator',
                'attributes' => [
                    'href' => '/user/users/14',
                    'media-type' => 'application/vnd.ez.api.User+xml',
                ],
            ],
            $result,
            'Invalid <Creator> element attributes.',
            false
        );
    }

    /**
     * Test if result contains Modifier element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsModifierElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Modifier',
            ],
            $result,
            'Invalid <Modifier> element.',
            false
        );
    }

    /**
     * Test if result contains Modifier element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsModifierAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Modifier',
                'attributes' => [
                    'href' => '/user/users/13',
                    'media-type' => 'application/vnd.ez.api.User+xml',
                ],
            ],
            $result,
            'Invalid <Modifier> element attributes.',
            false
        );
    }

    /**
     * Test if result contains ContentTypes element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypesElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypes',
            ],
            $result,
            'Invalid <ContentTypes> element.',
            false
        );
    }

    /**
     * Test if result contains ContentTypes element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentTypesAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'ContentTypes',
                'attributes' => [
                    'href' => '/content/typegroups/42/types',
                    'media-type' => 'application/vnd.ez.api.ContentTypeInfoList+xml',
                ],
            ],
            $result,
            'Invalid <ContentTypes> attributes.',
            false
        );
    }

    /**
     * Get the ContentTypeGroup visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeGroup
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentTypeGroup();
    }
}
