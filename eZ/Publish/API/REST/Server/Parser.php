<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server;
use eZ\Publish\API\REST\Server\Values;

/**
 * Base class for input parser
 */
abstract class Parser
{
    /**
     * Parse input structure
     *
     * @param string $body
     * @return mixed
     */
    abstract public function parse( $body );
}

