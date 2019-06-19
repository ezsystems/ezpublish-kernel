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
                [
                    'id' => 42,
                    'sourceContentInfo' => new ContentInfo(
                        [
                            'id' => 1,
                        ]
                    ),
                    'destinationContentInfo' => new ContentInfo(
                        [
                            'id' => 2,
                        ]
                    ),
                    'type' => Content\Relation::FIELD,
                    'sourceFieldDefinitionIdentifier' => 'relation_field',
                ]
            ),
            1,
            1
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadVersionRelation',
            [
                'contentId' => $relation->contentId,
                'versionNumber' => $relation->versionNo,
                'relationId' => $relation->relation->id,
            ],
            "/content/objects/{$relation->contentId}/versions/{$relation->versionNo}/relations/{$relation->relation->id}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            ['contentId' => $relation->contentId],
            "/content/objects/{$relation->contentId}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadContent',
            ['contentId' => $relation->relation->getDestinationContentInfo()->id],
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
            [
                'tag' => 'Relation',
                'children' => [
                    'less_than' => 5,
                    'greater_than' => 3,
                ],
            ],
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
            [
                'tag' => 'Relation',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.Relation+xml',
                    'href' => '/content/objects/1/versions/1/relations/42',
                ],
            ],
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
            [
                'tag' => 'SourceContent',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                    'href' => '/content/objects/1',
                ],
            ],
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
            [
                'tag' => 'DestinationContent',
                'attributes' => [
                    'media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                    'href' => '/content/objects/2',
                ],
            ],
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
            [
                'tag' => 'SourceFieldDefinitionIdentifier',
                'content' => 'relation_field',
            ],
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
            [
                'tag' => 'RelationType',
                'content' => 'ATTRIBUTE',
            ],
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
