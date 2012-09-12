<?php
/**
 * File containing the ContentType parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Input\Parser;
use eZ\Publish\Core\REST\Client\Input\ParserTools;
use eZ\Publish\Core\REST\Client\ContentTypeService;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;

use eZ\Publish\Core\REST\Client\Values;

/**
 * Parser for ContentType
 */
class FieldDefinitionList extends Parser
{
    /**
     * @var eZ\Publish\Core\REST\Client\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @var eZ\Publish\Core\REST\Client\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param ParserTools $parserTools
     * @param ContentTypeService $contentTypeService
     */
    public function __construct( ParserTools $parserTools, ContentTypeService $contentTypeService )
    {
        $this->parserTools = $parserTools;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\Content\ContentType
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $fieldDefinitionReferences = array();

        foreach ( $data['FieldDefinition'] as $fieldDefinitionData )
        {
            $fieldDefinitionReferences[] = $this->parserTools->parseObjectElement(
                $fieldDefinitionData,
                $parsingDispatcher
            );
        }

        return new Values\FieldDefinitionList(
            $this->contentTypeService,
            $fieldDefinitionReferences
        );
    }

    /**
     * Parses the content types status from $contentTypeStatus
     *
     * @param string $contentTypeStatus
     * @return int
     */
    protected function parseStatus( $contentTypeStatus )
    {
        switch ( strtoupper( $contentTypeStatus ) )
        {
            case 'DEFINED':
                return Values\ContentType\ContentType::STATUS_DEFINED;
            case 'DRAFT':
                return Values\ContentType\ContentType::STATUS_DRAFT;
            case 'MODIFIED':
                return Values\ContentType\ContentType::STATUS_MODIFIED;
        }

        throw new \RuntimeException( "Unknown ContentType status '{$contentTypeStatus}.'" );
    }

    /**
     * Parses the default sort field from the given $defaultSortFieldString
     *
     * @param string $defaultSortFieldString
     * @return int
     */
    protected function parseDefaultSortField( $defaultSortFieldString )
    {
        switch ( $defaultSortFieldString )
        {
            case 'PATH':
                return Values\Content\Location::SORT_FIELD_PATH;
            case 'PUBLISHED':
                return Values\Content\Location::SORT_FIELD_PUBLISHED;
            case 'MODIFIED':
                return Values\Content\Location::SORT_FIELD_MODIFIED;
            case 'SECTION':
                return Values\Content\Location::SORT_FIELD_SECTION;
            case 'DEPTH':
                return Values\Content\Location::SORT_FIELD_DEPTH;
            case 'CLASS_IDENTIFIER':
                return Values\Content\Location::SORT_FIELD_CLASS_IDENTIFIER;
            case 'CLASS_NAME':
                return Values\Content\Location::SORT_FIELD_CLASS_NAME;
            case 'PRIORITY':
                return Values\Content\Location::SORT_FIELD_PRIORITY;
            case 'NAME':
                return Values\Content\Location::SORT_FIELD_NAME;
            case 'MODIFIED_SUBNODE':
                return Values\Content\Location::SORT_FIELD_MODIFIED_SUBNODE;
            case 'NODE_ID':
                return Values\Content\Location::SORT_FIELD_NODE_ID;
            case 'CONTENTOBJECT_ID':
                return Values\Content\Location::SORT_FIELD_CONTENTOBJECT_ID;
        }

        throw new \RuntimeException( "Unknown default sort field: '{$defaultSortField}'." );
    }

    /**
     * Parses the default sort order from the given $defaultSortOrderString
     *
     * @param string $defaultSortOrderString
     * @return int
     */
    protected function parseDefaultSortOrder( $defaultSortOrderString )
    {
        switch ( strtoupper( $defaultSortOrderString ) )
        {
            case 'ASC':
                return Values\Content\Location::SORT_ORDER_ASC;
            case 'DESC':
                return Values\Content\Location::SORT_ORDER_DESC;
        }

        throw new \RuntimeException( "Unknown default sort order: '{$defaultSortOrderString}'." );
    }
}
