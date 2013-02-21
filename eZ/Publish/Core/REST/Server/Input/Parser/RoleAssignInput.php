<?php
/**
 * File containing the RoleAssignInput parser class
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

use eZ\Publish\Core\REST\Server\Values\RoleAssignment;

/**
 * Parser for RoleAssignInput
 */
class RoleAssignInput extends Base
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
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignment
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'Role', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'Role' element for RoleAssignInput." );
        }

        if ( !is_array( $data['Role'] ) || !array_key_exists( '_href', $data['Role'] ) )
        {
            throw new Exceptions\Parser( "Invalid 'Role' element for RoleAssignInput." );
        }

        try
        {
            $matches = $this->urlHandler->parse( 'role', $data['Role']['_href'] );
        }
        catch ( Exceptions\InvalidArgumentException $e )
        {
            throw new Exceptions\Parser( 'Invalid format for <Role> reference in <RoleAssignInput>.' );
        }

        // @todo XSD says that limitation is mandatory, but roles can be assigned without limitations
        $limitation = null;
        if ( array_key_exists( 'limitation', $data ) && is_array( $data['limitation'] ) )
        {
            $limitation = $this->parserTools->parseLimitation( $data['limitation'] );
        }

        return new RoleAssignment( $matches['role'], $limitation );
    }
}
