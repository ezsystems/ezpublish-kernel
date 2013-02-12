<?php
/**
 * File containing the Field class for content type
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Type;

use eZ\Publish\SPI\Persistence\ValueObject;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * @todo Do we need a FieldDefinitionCreateStruct?
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
    public $description = array();

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
     * A map of field type constraints.
     * 2 constraints are available (as keys):
     *   - validators
     *   - fieldSettings
     *
     * @var \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public $fieldTypeConstraints;

    /**
     * Default value of the field
     *
     * @var \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public $defaultValue;

    /**
     * @todo: Document
     *
     * @var boolean
     */
    public $isSearchable;

    /**
     * Constructor
     */
    public function __construct( array $properties = array() )
    {
        $this->fieldTypeConstraints = new FieldTypeConstraints;
        $this->defaultValue = new FieldValue;
        parent::__construct( $properties );
    }
}
