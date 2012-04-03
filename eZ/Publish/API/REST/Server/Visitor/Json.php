<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Visitor;
use eZ\Publish\API\REST\Server\Visitor;

class Json extends Visitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param mixed $data
     * @return string
     */
    public function visit( $data )
    {
        return "Hello World!";
    }
}

