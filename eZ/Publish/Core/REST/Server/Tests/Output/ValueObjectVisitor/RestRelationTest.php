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
use eZ\Publish\Core\Repository\Values\Content;
use eZ\Publish\Core\REST\Server\Values\RestRelation;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestRelationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestRelation visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $relation = new RestRelation(
            new Content\Relation(
                array(
                    'id' => 42,
                    'sourceContentInfo' => new ContentInfo(
                        array(
                            'id' => 1,
                        )
                    ),
                    'destinationContentInfo' => new ContentInfo(
                        array(
                            'id' => 2,
                        )
                    ),
                    'type' => Content\Relation::FIELD,
                    'sourceFieldDefinitionIdentifier' => 'relation_field',
                )
            ),
            1,
            1
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadVersionRelation',
            array(
                'contentId' => $relation->contentId,
                'versionNumber' => $relation->versionNo,
                'relationId' => $relation->relation->id,
            ),
            "/content/objects/{$relation->contentId}/versions/{$relation->versionNo}/relations/{$relation->relation->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            array('contentId' => $relation->contentId),
            "/content/objects/{$relation->contentId}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            array('contentId' => $relation->relation->getDestinationContentInfo()->id),
            "/content/objects/{$relation->relation->getDestinationContentInfo()->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $relation
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains Relation element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRelationElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Relation',
                'children' => array(
                    'less_than' => 5,
                    'greater_than' => 3,
                ),
            ),
            $result,
            'Invalid <Relation> element.',
            false
        );
    }

    /**
     * Test if result contains Relation element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRelationAttributes($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'Relation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Relation+xml',
                    'href' => '/content/objects/1/versions/1/relations/42',
                ),
            ),
            $result,
            'Invalid <Relation> attributes.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSourceContentElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'SourceContent',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                    'href' => '/content/objects/1',
                ),
            ),
            $result,
            'Invalid or non-existing <Relation> SourceContent element.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDestinationContentElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'DestinationContent',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                    'href' => '/content/objects/2',
                ),
            ),
            $result,
            'Invalid or non-existing <Relation> DestinationContent element.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSourceFieldDefinitionIdentifierElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'SourceFieldDefinitionIdentifier',
                'content' => 'relation_field',
            ),
            $result,
            'Invalid or non-existing <Relation> SourceFieldDefinitionIdentifier value element.',
            false
        );
    }

    /**
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRelationTypeElement($result)
    {
        $this->assertXMLTag(
            array(
                'tag' => 'RelationType',
                'content' => 'ATTRIBUTE',
            ),
            $result,
            'Invalid or non-existing <Relation> RelationType value element.',
            false
        );
    }

    /**
     * Get the Relation visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestRelation
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestRelation();
    }
}
