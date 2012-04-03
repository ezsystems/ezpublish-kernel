<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server;

abstract class Generator
{

    /**
     * Get media type
     *
     * @param string $name
     * @param string $type
     * @return string
     */
    protected function getMediaType( $name, $type )
    {
        return "application/vnd.ez.api.{$name}+{$type}";
    }
}

