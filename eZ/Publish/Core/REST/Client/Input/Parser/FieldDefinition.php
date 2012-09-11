<?php
/**
 * File containing the FieldDefinition parser class
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
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Parser for Version
 *
 * @todo Integrate FieldType fromHash()
 * @todo Caching for extracted embedded objects
 */
class FieldDefinition extends Parser
{
    /**
     * @var eZ\Publish\Core\REST\Client\Input\ParserTools
     */
    protected $parserTools;

    /**
     * @param eZ\Publish\Core\REST\Client\Input\Parser\VersionInfo $versionInfoParser
     */
    public function __construct( ParserTools $parserTools )
    {
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     * @todo Error handling
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        return new Values\ContentType\FieldDefinition( array(
            'id' => $data['_href'],
            'identifier' => $data['identifier'],
            'fieldTypeIdentifier' => $data['fieldType'],
            'fieldGroup' => $data['fieldGroup'],
            'position' => (int)$data['position'],
            'isTranslatable' => $this->parserTools->parseBooleanValue( $data['isTranslatable'] ),
            'isRequired' => $this->parserTools->parseBooleanValue( $data['isRequired'] ),
            'isInfoCollector' => $this->parserTools->parseBooleanValue( $data['isInfoCollector'] ),
            'isSearchable' => $this->parserTools->parseBooleanValue( $data['isSearchable'] ),
            'names' => $this->parserTools->parseTranslatableList( $data['names'] ),
            'descriptions' => $this->parserTools->parseTranslatableList( $data['descriptions'] ),

            // TODO: Call fromHash() here
            'defaultValue' => $data['defaultValue'],
        ) );
    }
}
