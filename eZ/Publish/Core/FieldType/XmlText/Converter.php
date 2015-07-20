<?php

/**
 * File containing the converter interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
     *
     * @return string|null
     */
    public function convert(DOMDocument $xmlDoc);
}
