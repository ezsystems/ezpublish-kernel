<?php
/**
 * File containing a RelationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Input\Parser;

use eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\API\Repository\Values;

class RelationTest extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentServiceMock;

    /**
     * Tests the section parser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation
     */
    public function testParse()
    {
        $relationParser = $this->getParser();

        $inputArray = array(
            '_href'      => '/content/objects/23/relations/32',
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

        $this->getContentServiceMock()->expects( $this->exactly( 2 ) )
            ->method( 'loadContentInfo' );

        $result = $relationParser->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * @depends testParse
     */
    public function testParsedId( $parsedRelation )
    {
        $this->assertEquals(
            '/content/objects/23/relations/32',
            $parsedRelation->id
        );
    }

    /**
     * @depends testParse
     */
    public function testParsedType( $parsedRelation )
    {
        $this->assertEquals(
            Values\Content\Relation::COMMON,
            $parsedRelation->type
        );
    }

    /**
     * Gets the section parser
     *
     * @return \eZ\Publish\Core\REST\Client\Input\Parser\Relation
     */
    protected function getParser()
    {
        return new Parser\Relation( $this->getContentServiceMock() );
    }

    /**
     * @return \eZ\Publish\Core\REST\Client\ContentService
     */
    protected function getContentServiceMock()
    {
        if ( !isset( $this->contentServiceMock ) )
        {
            $this->contentServiceMock = $this->getMock(
                'eZ\\Publish\\Core\\REST\\Client\\ContentService',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentServiceMock;
    }
}
