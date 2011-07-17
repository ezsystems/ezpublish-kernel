<?php
/**
 * File containing the Field class for content type
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Type;
use ezp\Persistence\AbstractValueObject;

/**
 * @todo Do we need a FieldDefitinitionCreateStruct?
 * @todo What about the field "is_searchable" in the legacy storage?
 */
class FieldDefinition extends AbstractValueObject
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
     * @var mixed
     * @todo What does this attribute refer to in the legacy storage? "category"?
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
    public $translatable;

    /**
     * Is the field required
     *
     * @var boolean
     */
    public $required;

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
     * @var mixed
     */
    public $defaultValue;
}
?>
