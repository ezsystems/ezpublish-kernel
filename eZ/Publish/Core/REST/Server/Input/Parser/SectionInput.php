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
use eZ\Publish\Core\REST\Common\Exceptions;

use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;

/**
 * Base class for input parser
 */
class SectionInput extends Base
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     * @return \eZ\Publish\API\Repository\Values\Content\SectionCreateStruct
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        if ( !array_key_exists( 'name', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'name' attribute for SectionInput." );
        }
        if ( !array_key_exists( 'identifier', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'identifier' attribute for SectionInput." );
        }

        return new SectionCreateStruct( array(
            'name'       => $data["name"],
            'identifier' => $data["identifier"],
        ) );
    }
}

