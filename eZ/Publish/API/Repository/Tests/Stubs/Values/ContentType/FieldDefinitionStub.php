<?php
/**
 * File containing the eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\FieldDefinitionStub class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
/**
 *
 * This class represents a field definition
 * @property-read $names calls getNames() or on access getName($language)
 * @property-read $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read $fieldSettings calls getFieldSettings()
 * @property-read mixed $validatorConfiguration calls getValidatorConfiguration()
 * @property-read int $id the id of the field definition
 * @property-read string $identifier the identifier of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read int $position the position of the field definition in the content type
 * @property-read string $fieldType String identifier of the field type
 * @property-read boolean $isTranslatable indicates if fields of this definition are translatable
 * @property-read boolean $isRequired indicates if this field is required in the content object
 * @property-read boolean $isSearchable indicates if the field is searchable
 * @property-read boolean $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 */
class FieldDefinitionStub extends FieldDefinition
{
    /**
     * the unique id of this field definition
     *
     * @var mixed
     */
    // parent::
    // protected $id;

    /**
     * Readable string identifier of a field definition
     *
     * @var string
     */
    // parent::
    // protected $identifier;

    /**
     * Contains the human readable name of this field in all provided languages
     * of the content type
     *
     * @var string[]
     */
    protected $names;

    /**
     * Contains the human readable description of the field
     *
     * @var string[]
     */
    protected $descriptions;

    /**
     * Field group name
     *
     * @var string
     */
    // parent::
    // protected $fieldGroup;

    /**
     * the position of the field definition in the content type
     *
     * @var int
     */
    // parent::
    // protected $position;

    /**
     * String identifier of the field type
     *
     * @var string
     */
    // parent::
    // protected $fieldType;

    /**
     * If the field is translatable
     *
     * @var boolean
     */
    // parent::
    // protected $isTranslatable;

    /**
     * Is the field required
     *
     * @var boolean
     */
    // parent::
    // protected $isRequired;

    /**
     * the flag if this field is used for information collection
     *
     * @var boolean
     */
    // parent::
    // protected $isInfoCollector;

    /**
     * Contains the validatorConfiguration of this field definition supported by the field type#
     *
     * @var \eZ\Publish\Core\FieldType\Validator[]
     */
    protected $validatorConfiguration;

    /**
     * Contains settings for the field definition supported by the field type
     *
     * @var array
     */
    protected $fieldSettings;

    /**
     * Default value of the field
     *
     * @var mixed
     */
    // parent::
    // protected $defaultValue;

    /**
     * Indicates if th the content is searchable by this attribute
     *
     * @var boolean
     */
    // parent::
    // protected $isSearchable;

    function __construct( array $data = array() )
    {
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * This method returns the human readable name of this field in all provided languages
     * of the content type
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * This method returns the name of the field in the given language
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    public function getName( $languageCode )
    {
        return $this->names[$languageCode];
    }

    /**
     * This method returns the human readable description of the field
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * This method returns the name of the field in the given language
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    public function getDescription( $languageCode )
    {
        return $this->descriptions[$languageCode];
    }

    /**
     * This method returns the validator configuration of this field definition supported by the field type
     *
     * @return mixed
     */
    public function getValidatorConfiguration()
    {
        return $this->validatorConfiguration;
    }

    /**
     * This method returns settings for the field definition supported by the field type
     *
     * @return mixed
     */
    public function getFieldSettings()
    {
        return $this->fieldSettings;
    }
}
