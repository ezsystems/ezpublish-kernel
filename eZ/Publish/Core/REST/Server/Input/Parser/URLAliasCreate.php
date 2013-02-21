<?php
/**
 * File containing the URLAliasCreate parser class
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

/**
 * Parser for URLAliasCreate
 */
class URLAliasCreate extends Base
{
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
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct( UrlHandler $urlHandler, ParserTools $parserTools )
    {
        parent::__construct( $urlHandler );
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return array
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( '_type', $data ) )
        {
            throw new Exceptions\Parser( "Missing '_type' value for URLAliasCreate." );
        }

        if ( $data['_type'] == 'LOCATION' )
        {
            if ( !array_key_exists( 'location', $data ) )
            {
                throw new Exceptions\Parser( "Missing 'location' value for URLAliasCreate." );
            }

            if ( !is_array( $data['location'] ) || !array_key_exists( '_href', $data['location'] ) )
            {
                throw new Exceptions\Parser( "Missing 'location' > '_href' attribute for URLAliasCreate." );
            }
        }
        else
        {
            if ( !array_key_exists( 'resource', $data ) )
            {
                throw new Exceptions\Parser( "Missing 'resource' value for URLAliasCreate." );
            }
        }

        if ( !array_key_exists( 'path', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'path' value for URLAliasCreate." );
        }

        if ( !array_key_exists( 'languageCode', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'languageCode' value for URLAliasCreate." );
        }

        if ( array_key_exists( 'alwaysAvailable', $data ) )
        {
            $data['alwaysAvailable'] = $this->parserTools->parseBooleanValue( $data['alwaysAvailable'] );
        }
        else
        {
            $data['alwaysAvailable'] = false;
        }

        if ( array_key_exists( 'forward', $data ) )
        {
            $data['forward'] = $this->parserTools->parseBooleanValue( $data['forward'] );
        }
        else
        {
            $data['forward'] = false;
        }

        return $data;
    }
}
