<?php

/**
 * File containing a RelationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Values;
use eZ\Publish\Core\REST\Client\ContentService;

class RelationTest extends BaseTest
{
    /** @var \eZ\Publish\Core\REST\Client\ContentService */
    protected $contentServiceMock;

    /**
     * Tests the section parser.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation
     */
    public function testParse()
    {
        $relationParser = $this->getParser();

        $inputArray = array(
            '_href' => '/content/objects/23/relations/32',
            '_media-type' => 'application/vnd.ez.api.Relation+xml',
            'SourceContent' => array(
                '_media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                '_href' => '/content/objects/23',
            ),
            'DestinationContent' => array(
                '_media-type' => 'application/vnd.ez.api.ContentInfo+xml',
                '_href' => '/content/objects/45',
            ),
            'RelationType' => 'COMMON',
        );

        $this->getContentServiceMock()->expects($this->exactly(2))
            ->method('loadContentInfo');

        $result = $relationParser->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertNotNull($result);

        return $result;
    }

    /**
     * @depends testParse
     */
    public function testParsedId($parsedRelation)
    {
        $this->assertEquals(
            '/content/objects/23/relations/32',
            $parsedRelation->id
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedType($parsedRelation)
    {
        $this->assertEquals(
            Values\Content\Relation::COMMON,
            $parsedRelation->type
        );
    }

    /**
     * Gets the section parser.
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Relation
     */
    protected function getParser()
    {
        return new Parser\Relation($this->getContentServiceMock());
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentServiceMock()
    {
        if (!isset($this->contentServiceMock)) {
            $this->contentServiceMock = $this->createMock(ContentService::class);
        }

        return $this->contentServiceMock;
    }
}
