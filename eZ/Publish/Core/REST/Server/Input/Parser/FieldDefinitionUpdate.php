<?php
/**
 * File containing the FieldDefinitionUpdate parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for FieldDefinitionUpdate
 */
class FieldDefinitionUpdate extends Base
{
    /**
     * ContentType service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Parser tools
     *
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Construct
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( UrlHandler $urlHandler, ContentTypeService $contentTypeService, ParserTools $parserTools )
    {
        parent::__construct( $urlHandler );
        $this->contentTypeService = $contentTypeService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $fieldDefinitionUpdate = $this->contentTypeService->newFieldDefinitionUpdateStruct();

        if ( array_key_exists( 'identifier', $data ) )
        {
            $fieldDefinitionUpdate->identifier = $data['identifier'];
        }

        // @todo XSD says that descriptions is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'names', $data ) )
        {
            if ( !is_array( $data['names'] ) || !array_key_exists( 'value', $data['names'] ) || !is_array( $data['names']['value'] ) )
            {
                throw new Exceptions\Parser( "Invalid 'names' element for FieldDefinitionUpdate." );
            }

            $fieldDefinitionUpdate->names = $this->parserTools->parseTranslatableList( $data['names'] );
        }

        // @todo XSD says that descriptions is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'descriptions', $data ) )
        {
            if ( !is_array( $data['descriptions'] ) || !array_key_exists( 'value', $data['descriptions'] ) || !is_array( $data['descriptions']['value'] ) )
            {
                throw new Exceptions\Parser( "Invalid 'descriptions' element for FieldDefinitionUpdate." );
            }

            $fieldDefinitionUpdate->descriptions = $this->parserTools->parseTranslatableList( $data['descriptions'] );
        }

        // @todo XSD says that fieldGroup is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'fieldGroup', $data ) )
        {
            $fieldDefinitionUpdate->fieldGroup = $data['fieldGroup'];
        }

        // @todo XSD says that position is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'position', $data ) )
        {
            $fieldDefinitionUpdate->position = (int)$data['position'];
        }

        // @todo XSD says that isTranslatable is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'isTranslatable', $data ) )
        {
            $fieldDefinitionUpdate->isTranslatable = $this->parserTools->parseBooleanValue( $data['isTranslatable'] );
        }

        // @todo XSD says that isRequired is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'isRequired', $data ) )
        {
            $fieldDefinitionUpdate->isRequired = $this->parserTools->parseBooleanValue( $data['isRequired'] );
        }

        // @todo XSD says that isInfoCollector is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'isInfoCollector', $data ) )
        {
            $fieldDefinitionUpdate->isInfoCollector = $this->parserTools->parseBooleanValue( $data['isInfoCollector'] );
        }

        // @todo XSD says that isSearchable is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'isSearchable', $data ) )
        {
            $fieldDefinitionUpdate->isSearchable = $this->parserTools->parseBooleanValue( $data['isSearchable'] );
        }

        // @todo XSD says that defaultValue is mandatory, but field definition can be updated without it
        if ( array_key_exists( 'defaultValue', $data ) )
        {
            $fieldDefinitionUpdate->defaultValue = $data['defaultValue'];
        }

        //@todo fieldSettings - Not specified
        //@todo validatorConfiguration - Not specified

        return $fieldDefinitionUpdate;
    }
}
