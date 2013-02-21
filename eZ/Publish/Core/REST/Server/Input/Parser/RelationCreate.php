<?php
/**
 * File containing the RelationCreate parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;

/**
 * Parser for RelationCreate
 */
class RelationCreate extends Base
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return mixed
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'Destination', $data ) || !is_array( $data['Destination'] ) )
        {
            throw new Exceptions\Parser( "Missing or invalid 'Destination' element for RelationCreate." );
        }

        if ( !array_key_exists( '_href', $data['Destination'] ) )
        {
            throw new Exceptions\Parser( "Missing '_href' attribute for Destination element in RelationCreate." );
        }

        $destinationParts = $this->urlHandler->parse( 'object', $data['Destination']['_href'] );
        return $destinationParts['object'];
    }
}
