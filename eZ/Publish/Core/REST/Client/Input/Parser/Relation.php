<?php
/**
 * File containing the Relation parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\Core\REST\Client\Values;
use eZ\Publish\Core\REST\Client\ContentService;

/**
 * Parser for Relation
 */
class Relation extends Parser
{
    /**
     * Content Service
     *
     * @var \eZ\Publish\Core\REST\Input\ContentService
     */
    protected $contentService;

    /**
     * @param \eZ\Publish\Core\REST\Input\ContentService $contentService
     */
    public function __construct( ContentService $contentService )
    {
        $this->contentService = $contentService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Relation\Version
     * @todo Error handling
     * @todo Should the related ContentInfo structs really be loaded here or do
     *       we need lazy loading for this?
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        return new Values\Content\Relation(
            array(
                'id' => $data['_href'],
                'sourceContentInfo' => $this->contentService->loadContentInfo(
                    $data['SourceContent']['_href']
                ),
                'destinationContentInfo' => $this->contentService->loadContentInfo(
                    $data['DestinationContent']['_href']
                ),
                'type' => $this->convertRelationType( $data['RelationType'] ),
                // @todo: Handle SourceFieldDefinitionIdentifier
            )
        );
    }

    /**
     * Converts the string representation of the relation type to its constant
     *
     * @param string $stringType
     *
     * @return int
     */
    protected function convertRelationType( $stringType )
    {
        switch ( strtoupper( $stringType ) )
        {
            case 'COMMON':
                return Values\Content\Relation::COMMON;
            case 'EMBED':
                return Values\Content\Relation::EMBED;
            case 'LINK':
                return Values\Content\Relation::LINK;
            case 'FIELD':
                return Values\Content\Relation::FIELD;
        }
        throw new \RuntimeException(
            sprintf( 'Unknown Relation type: "%s"', $stringType )
        );
    }
}
