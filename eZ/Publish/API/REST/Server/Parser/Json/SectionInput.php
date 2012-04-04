<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Parser\Json;
use eZ\Publish\API\REST\Server\Parser\Json;
use eZ\Publish\API\REST\Server\Values;

/**
 * Base class for input parser
 */
class SectionInput extends Json
{
    /**
     * Parse input structure
     *
     * @param string $body
     * @return mixed
     */
    public function parse( $body )
    {
        $data = json_decode( $body );
        $createStruct = new SectionCreateStruct( array(
            $data->
        ) );
    }
}

