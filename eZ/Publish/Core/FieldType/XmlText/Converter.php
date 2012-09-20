<?php
/**
 * File containing the eZ\Publish\Core\FieldType\XmlText\Converter interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

interface Converter
{
    /**
     * Convert $xmlString to or from internal representation
     *
     * @param string $xmlString
     * @return string
     */
    public function convert( $xmlString );
}
