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
use eZ\Publish\Core\REST\Server\Values\VersionList;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;

class VersionListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the VersionList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $versionInfo = new VersionInfo(
            array(
                'versionNo' => 1,
                'contentInfo' => new ContentInfo(array('id' => 12345)),
            )
        );
        $versionList = new VersionList(array($versionInfo), '/some/path');

        $this->addRouteExpectation(
            'ezpublish_rest_loadContentInVersion',
            array(
                'contentId' => $versionInfo->contentInfo->id,
                'versionNumber' => $versionInfo->versionNo,
            ),
            "/content/objects/{$versionInfo->contentInfo->id}/versions/{$versionInfo->versionNo}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains VersionList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsVersionListElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'VersionList',
            ),
            $result,
            'Invalid <VersionList> element.',
            false
        );
    }

    /**
     * Test if result contains VersionList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsVersionListAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'VersionList',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.VersionList+xml',
                    'href' => '/some/path',
                ),
            ),
            $result,
            'Invalid <VersionList> attributes.',
            false
        );
    }

    /**
     * Test if VersionList visitor visits the children.
     */
    public function testVersionListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $versionList = new VersionList(
            array(
                new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42,
                            )
                        ),
                        'versionNo' => 1,
                    )
                ),
                new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'id' => 42,
                            )
                        ),
                        'versionNo' => 2,
                    )
                ),
            ),
            '/some/path'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf('eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo'));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $versionList
        );
    }

    /**
     * Get the VersionList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\VersionList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\VersionList();
    }
}
