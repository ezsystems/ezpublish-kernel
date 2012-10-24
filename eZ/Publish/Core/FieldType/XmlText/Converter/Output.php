<?php
/**
 * File containing the Output converter interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

use DOMDocument;

interface Output
{
    /**
     * Converts $xmlDoc (internal XML representation) to a string
     *
     * @param \DOMDocument $xmlDoc
     * @return string
     */
    public function convert( DOMDocument $xmlDoc );
}
