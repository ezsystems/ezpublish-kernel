<?php
/**
 * File containing the LocationCreate parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\API\Repository\LocationService;

/**
 * Parser for LocationCreate
 */
class LocationCreate extends Base
{
    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

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
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( UrlHandler $urlHandler, LocationService $locationService, ParserTools $parserTools )
    {
        parent::__construct( $urlHandler );
        $this->locationService = $locationService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'ParentLocation', $data ) || !is_array( $data['ParentLocation'] ) )
        {
            throw new Exceptions\Parser( "Missing or invalid 'ParentLocation' element for LocationCreate." );
        }

        if ( !array_key_exists( '_href', $data['ParentLocation'] ) )
        {
            throw new Exceptions\Parser( "Missing '_href' attribute for ParentLocation element in LocationCreate." );
        }

        $locationHref = $this->urlHandler->parse( 'location', $data['ParentLocation']['_href'] );
        $locationHrefParts = explode( '/', $locationHref['location'] );

        $locationCreateStruct = $this->locationService->newLocationCreateStruct(
            array_pop( $locationHrefParts )
        );

        if ( array_key_exists( 'priority', $data ) )
        {
            $locationCreateStruct->priority = (int)$data['priority'];
        }

        if ( array_key_exists( 'hidden', $data ) )
        {
            $locationCreateStruct->hidden = $this->parserTools->parseBooleanValue( $data['hidden'] );
        }

        if ( array_key_exists( 'remoteId', $data ) )
        {
            $locationCreateStruct->remoteId = $data['remoteId'];
        }

        if ( !array_key_exists( 'sortField', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'sortField' element for LocationCreate." );
        }

        $locationCreateStruct->sortField = $this->parserTools->parseDefaultSortField( $data['sortField'] );

        if ( !array_key_exists( 'sortOrder', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'sortOrder' element for LocationCreate." );
        }

        $locationCreateStruct->sortOrder = $this->parserTools->parseDefaultSortOrder( $data['sortOrder'] );

        return $locationCreateStruct;
    }
}
