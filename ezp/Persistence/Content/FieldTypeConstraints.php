<?php
/**
 * File containing the FieldTypeConstraints class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

class FieldTypeConstraints extends ValueObject
{
    /**
     * Array of validators.
     * Key is the FQN fo the validator class.
     * Value is a hash like described in {@link \ezp\Content\FieldType\Validator::$constraints}
     *
     * @see \ezp\Content\FieldType\Validator::$constraints
     * @var array
     */
    public $validators;

    /**
     * Collection of field settings as it is supported by dedicated field type,
     * and set in {@link \ezp\Content\Type\FieldDefinition}.
     * Collection is indexed by setting name.
     *
     * @see \ezp\Content\FieldType::$fieldSettings
     * @var \ezp\Content\FieldType\FieldSettings
     */
    public $fieldSettings;
}
