<?php
/**
 * File containing the Handler base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Input\Handler;
use eZ\Publish\API\REST\Common\Input\Handler;

/**
 * Input format handler base class
 */
class Json extends Handler
{
    /**
     * Converts the given string to an array structure
     *
     * @param string $string
     * @return array
     */
    public function convert( $string )
    {
        return json_decode( $string, true );
    }
}
