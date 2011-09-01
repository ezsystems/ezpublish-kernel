<?php
/**
 * File containing the Field class for content type
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Type;
use ezp\Persistence\ValueObject;

/**
 * @todo Do we need a FieldDefitinitionCreateStruct?
 * @todo What about the field "is_searchable" in the legacy storage?
 * @todo What about the "serialized_data_text" field in legacy storage?
 */
class FieldDefinition extends ValueObject
{
    /**
     * Primary key
     *
     * @var mixed
     */
    public $id;

    /**
     * Name
     *
     * @var string[]
     */
    public $name;

    /**
     * Description
     *
     * @var string[]
     */
    public $description;

    /**
     * Readable string identifier of a field definition
     *
     * @var string
    */
    public $identifier;

    /**
     * Field group name
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * Position
     *
     * @var int
     */
    public $position;

    /**
     * String identifier of the field type
     *
     * @var string
     */
    public $fieldType;

    /**
     * If the field type is translatable
     *
     * @var boolean
     */
    public $isTranslatable;

    /**
     * Is the field required
     *
     * @var boolean
     */
    public $isRequired;

    /**
     * Just a flag
     *
     * @var boolean
     */
    public $isInfoCollector;

    /**
     * A map (hash) of field type constraints
     *
     * @var array
     */
    public $fieldTypeConstraints;

    /**
     * Default value of the field
     *
     * @var \ezp\Persistence\Content\FieldValue
     */
    public $defaultValue;
}
?>
