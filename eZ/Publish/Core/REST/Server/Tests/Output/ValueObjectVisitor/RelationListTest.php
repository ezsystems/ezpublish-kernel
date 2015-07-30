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
use eZ\Publish\Core\REST\Server\Values\RelationList;
use eZ\Publish\Core\Repository\Values\Content;

class RelationListTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RelationList visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $relationList = new RelationList(array(), 42, 21);

        $this->addRouteExpectation(
            'ezpublish_rest_loadVersionRelations',
            array(
                'contentId' => $relationList->contentId,
                'versionNumber' => $relationList->versionNo,
            ),
            "/content/objects/{$relationList->contentId}/versions/{$relationList->versionNo}/relations"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $relationList
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Relations element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRelationsElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Relations',
            ),
            $result,
            'Invalid <Relations> element.',
            false
        );
    }

    /**
     * Test if result contains Relations element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRelationsAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Relations',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.RelationList+xml',
                    'href' => '/content/objects/42/versions/21/relations',
                ),
            ),
            $result,
            'Invalid <Relations> attributes.',
            false
        );
    }

    /**
     * Test if RelationList visitor visits the children.
     */
    public function testRelationListVisitsChildren()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $relationList = new RelationList(
            array(
                new Content\Relation(),
                new Content\Relation(),
            ),
            23,
            1
        );

        $this->getVisitorMock()->expects($this->exactly(2))
            ->method('visitValueObject')
            ->with($this->isInstanceOf('eZ\\Publish\\Core\\REST\\Server\\Values\\RestRelation'));

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $relationList
        );
    }

    /**
     * Get the RelationList visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RelationList
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RelationList();
    }
}
