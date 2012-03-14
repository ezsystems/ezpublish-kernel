<?php
/**
 * File containing the Value abstract class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Abstract class for all field value classes.
 * A field value object is to be understood with associated field type
 */
abstract class Value extends ValueObject
{
    /**
     * Returns the title of the current field value.
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->__toString();
    }
}
