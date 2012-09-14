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

use eZ\Publish\Core\REST\Server\Values\RoleAssignment;

/**
 * Base class for input parser
 */
class RoleAssignInput extends Base
{
    /**
     * Constructor
     *
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( UrlHandler $urlHandler )
    {
        parent::__construct( $urlHandler );
    }

    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
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

        $limitation = null;
        if ( array_key_exists( 'limitation', $data ) && is_array( $data['limitation'] ) )
        {
            $limitation = $parsingDispatcher->parse( $data['limitation'], $data['limitation']['_media-type'] );
        }

        return new RoleAssignment( $matches['role'], $limitation );
    }
}

