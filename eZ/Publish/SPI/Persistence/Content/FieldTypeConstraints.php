<?php
/**
 * File containing the FieldTypeConstraints class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\ValueObject;

class FieldTypeConstraints extends ValueObject
{
    /**
     * Array of validators.
     * Key is the FQN for the validator class.
     * Value is a hash like described in {@link \eZ\Publish\Core\FieldType\Validator::$constraints}
     *
     * @see \eZ\Publish\Core\FieldType\Validator::$constraints
     * @var array
     */
    public $validators;

    /**
     * Collection of field settings as it is supported by dedicated field type,
     * and set in {@link \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition}.
     * Collection is indexed by setting name.
     *
     * @see \eZ\Publish\Core\FieldType\FieldType::$fieldSettings
     *
     * @TODO Should not use Core class
     * @var \eZ\Publish\Core\FieldType\FieldSettings
     */
    public $fieldSettings;
}
