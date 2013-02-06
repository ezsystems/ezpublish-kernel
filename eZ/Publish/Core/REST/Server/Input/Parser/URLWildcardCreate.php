<?php
/**
 * File containing the URLWildcardCreate parser class
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
 * Parser for URLWildcardCreate
 */
class URLWildcardCreate extends Base
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
        if ( !array_key_exists( 'sourceUrl', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'sourceUrl' value for URLWildcardCreate." );
        }

        if ( !array_key_exists( 'destinationUrl', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'destinationUrl' value for URLWildcardCreate." );
        }

        if ( !array_key_exists( 'forward', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'forward' value for URLWildcardCreate." );
        }

        $data['forward'] = $this->parserTools->parseBooleanValue( $data['forward'] );

        return $data;
    }
}
