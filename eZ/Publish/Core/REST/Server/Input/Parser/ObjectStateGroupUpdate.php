<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\ObjectStateService;

/**
 * Base class for input parser
 */
class ObjectStateGroupUpdate extends Base
{
    /**
     * Object state service
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * Construct from object state service
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ObjectStateService $objectStateService
     */
    public function __construct( UrlHandler $urlHandler, ObjectStateService $objectStateService )
    {
        parent::__construct( $urlHandler );
        $this->objectStateService = $objectStateService;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'identifier', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'identifier' attribute for ObjectStateGroupUpdate." );
        }

        if ( !array_key_exists( 'defaultLanguageCode', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'defaultLanguageCode' attribute for ObjectStateGroupUpdate." );
        }

        if ( !array_key_exists( 'names', $data ) || !is_array( $data['names'] ) )
        {
            throw new Exceptions\Parser( "Missing or invalid 'names' element for ObjectStateGroupUpdate." );
        }

        if ( !array_key_exists( 'value', $data['names'] ) || !is_array( $data['names']['value'] ) )
        {
            throw new Exceptions\Parser( "Missing or invalid 'names' element for ObjectStateGroupUpdate." );
        }

        $objectStateGroupUpdateStruct = $this->objectStateService->newObjectStateGroupUpdateStruct();
        $objectStateGroupUpdateStruct->identifier = $data['identifier'];
        $objectStateGroupUpdateStruct->defaultLanguageCode = $data['defaultLanguageCode'];

        foreach ( $data['names']['value'] as $nameData )
        {
            if ( !array_key_exists( '_languageCode', $nameData ) )
            {
                throw new Exceptions\Parser( "Missing '_languageCode' attribute for ObjectStateGroupUpdate name." );
            }

            if ( !array_key_exists( '#text', $nameData ) )
            {
                throw new Exceptions\Parser( "Missing value for ObjectStateGroupUpdate name." );
            }

            $objectStateGroupUpdateStruct->names[$nameData['_languageCode']] = $nameData['#text'];
        }

        if ( array_key_exists( 'descriptions', $data ) && is_array( $data['descriptions'] ) )
        {
            if ( array_key_exists( 'value', $data['descriptions'] ) && is_array( $data['descriptions']['value'] ) )
            {
                foreach ( $data['descriptions']['value'] as $descriptionData )
                {
                    if ( !array_key_exists( '_languageCode', $descriptionData ) )
                    {
                        throw new Exceptions\Parser( "Missing '_languageCode' attribute for ObjectStateGroupUpdate description." );
                    }

                    if ( !array_key_exists( '#text', $descriptionData ) )
                    {
                        throw new Exceptions\Parser( "Missing value for ObjectStateGroupUpdate description." );
                    }

                    $objectStateGroupUpdateStruct->descriptions[$descriptionData['_languageCode']] = $descriptionData['#text'];
                }
            }
        }

        return $objectStateGroupUpdateStruct;
    }
}
