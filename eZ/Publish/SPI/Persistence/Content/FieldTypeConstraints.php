<?php
/**
 * File containing the FieldTypeConstraints class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class FieldTypeConstraints extends ValueObject
{
    /**
     * Validator settings compatible to the corresponding FieldType
     *
     * This property contains validator settings as defined by the fields type.
     * Note that contents of this property must be serializable and exportable
     * (i.e. no circular references, resources and friends).
     *
     * @see \eZ\Publish\SPI\FieldType\FieldType
     * @var mixed
     */
    public $validators;

    /**
     * Field settings compatible to the corresponding FieldType
     *
     * This property contains field settings as defined by the fields type.
     * Note that contents of this property must be serializable and exportable
     * (i.e. no circular references, resources and friends).
     *
     * @see \eZ\Publish\SPI\FieldType\FieldType
     * @var mixed
     */
    public $fieldSettings;
}
