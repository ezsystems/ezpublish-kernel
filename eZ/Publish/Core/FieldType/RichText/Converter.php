<?php
/**
 * File containing the converter interface.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText;

use DOMDocument;

/**
 * Interface for rich text conversion.
 */
interface Converter
{
    /**
     * Converts given $xmlDoc into another \DOMDocument object
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return \DOMDocument
     */
    public function convert( DOMDocument $xmlDoc );
}
