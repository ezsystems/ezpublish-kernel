<?php

/**
 * File containing the converter interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
     * Converts given $xmlDoc into another \DOMDocument object.
     *
     * @param \DOMDocument $xmlDoc
     *
     * @return \DOMDocument
     */
    public function convert(DOMDocument $xmlDoc);
}
