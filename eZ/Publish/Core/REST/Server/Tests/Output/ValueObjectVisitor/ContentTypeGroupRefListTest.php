<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList;

class ContentTypeGroupRefListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentTypeGroupRefList visitor.
     *
     * @todo coverage test with one group (can't be deleted)
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $contentTypeGroupRefList = new ContentTypeGroupRefList(
            new ContentType(
                [
                    'id' => 42,
                    'fieldDefinitions' => [],
                ]
            ),
            [
                new ContentTypeGroup(
                    [
                        'id' => 1,
                    ]
                ),
                new ContentTypeGroup(
                    [
                        'id' => 2,
                    ]
                ),
            ]
        );

        $this->addRouteExpectation(
            'ezpublish_rest_listContentTypesForGroup',
            ['contentTypeGroupId' => $contentTypeGroupRefList->contentType->id],
            "/content/types/{$contentTypeGroupRefList->contentType->id}/groups"
        );

        // first iteration
        $this->addRouteExpectation(
            'ezpublish_rest_loadContentTypeGroup',
            ['contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[0]->id],
            "/content/typegroups/{$contentTypeGroupRefList->contentTypeGroups[0]->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_unlinkContentTypeFromGroup',
            [
                'contentTypeId' => $contentTypeGroupRefList->contentType->id,
                'contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[0]->id,
            ],
            "/content/types/{$contentTypeGroupRefList->contentType->id}/groups/{$contentTypeGroupRefList->contentTypeGroups[0]->id}"
        );

        // second iteration
        $this->addRouteExpectation(
            'ezpublish_rest_loadContentTypeGroup',
            ['contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[1]->id],
            "/content/typegroups/{$contentTypeGroupRefList->contentTypeGroups[1]->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_unlinkContentTypeFromGroup',
            [
                'contentTypeId' => $contentTypeGroupRefList->contentType->id,
                'contentTypeGroupId' => $contentTypeGroupRefList->contentTypeGroups[1]->id,
            ],
            "/content/types/{$contentTypeGroupRefList->contentType->id}/groups/{$contentTypeGroupRefList->contentTypeGroups[1]->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $contentTypeGroupRefList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentTypeGroupRefListHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList[@href="/content/types/42/groups"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testContentTypeGroupRefListMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList[@media-type="application/vnd.ez.api.ContentTypeGroupRefList+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1][@href="/content/typegroups/1"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1][@media-type="application/vnd.ez.api.ContentTypeGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefUnlinkHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1]/unlink[@href="/content/types/42/groups/1"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testFirstContentTypeGroupRefUnlinkMethodCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[1]/unlink[@method="DELETE"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2][@href="/content/typegroups/2"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefMediaTypeCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2][@media-type="application/vnd.ez.api.ContentTypeGroup+xml"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefUnlinkHrefCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2]/unlink[@href="/content/types/42/groups/2"]');
    }

    /**
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     */
    public function testSecondContentTypeGroupRefUnlinkMethodCorrect(\DOMDocument $dom)
    {
        $this->assertXPath($dom, '/ContentTypeGroupRefList/ContentTypeGroupRef[2]/unlink[@method="DELETE"]');
    }

    /**
     * Get the ContentTypeGroupRefList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\ContentTypeGroupRefList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\ContentTypeGroupRefList();
    }
}
