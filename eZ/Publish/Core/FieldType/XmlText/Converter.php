<?php
/**
 * File containing the converter interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use DOMDocument;

/**
 * Interface for rich text conversion from internal XML representation to a string to display.
 */
interface Converter
{
    /**
     * Converts $xmlDoc (internal XML representation) to a string.
     * Returned value can :
     *  - The rendered string
     *  - null if a partial work is done on $xmlDoc (useful for pre-conversion).
     *
     * @param \DOMDocument $xmlDoc
     * @return string|null
     */
    public function convert( DOMDocument $xmlDoc );
}
