<?php
/**
 * File containing the Input converter interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\Converter;

interface Input
{
    /**
     * Converts $input to internal XML representation
     *
     * @param mixed $input
     * @return string
     */
    public function convert( $input );
}
