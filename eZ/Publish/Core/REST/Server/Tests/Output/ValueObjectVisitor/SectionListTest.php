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
use eZ\Publish\Core\REST\Server\Values\SectionList;
use eZ\Publish\API\Repository\Values\Content;

class SectionListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the SectionList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $sectionList = new SectionList([], '/content/sections');

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains SectionList element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionListElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'SectionList',
            ],
            $result,
            'Invalid <SectionList> element.',
            false
        );
    }

    /**
     * Test if result contains SectionList element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSectionListAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'SectionList',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.SectionList+xml',
                    'href' => '/content/sections',
                ],
            ],
            $result,
            'Invalid <SectionList> attributes.',
            false
        );
    }

    /**
     * Test if SectionList visitor visits the children.
     */
    public function testSectionListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $sectionList = new SectionList(
            [
                new Content\Section(),
                new Content\Section(),
            ],
            '/content/sections'
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf(Content\Section::class));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $sectionList
        );
    }

    /**
     * Get the SectionList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\SectionList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\SectionList();
    }
}
