<?php
/**
 * File containing the Image converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

class Media extends BinaryFile
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return Image
     */
    public static function create()
    {
        return new self;
    }
}
