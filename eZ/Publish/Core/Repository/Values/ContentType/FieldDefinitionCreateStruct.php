<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct as APIFieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\Validator;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class is used to create a field definition
 *
 * @property $names the collection of names with languageCode keys.
 *           the calls <code>$fdcs->names[$language] = "abc"</code> and <code>$fdcs->setName("abc",$language)</code> are equivalent
 * @property $descriptions the collection of descriptions with languageCode keys.
 *           the calls <code>$fdcs->descriptions[$language] = "abc"</code> and <code>$fdcs->setDescription("abc",$language)</code> are equivalent
 * @property $validators the collection of validators with the validator names as keys
 * @property $fieldSettings the collection of fieldSettings
 */
class FieldDefinitionCreateStruct extends APIFieldDefinitionCreateStruct
{
    /**
     * Holds the collection of names with languageCode keys
     *
     * @var array
     */
    public $names = array();

    /**
     * Holds the collection of descriptions with languageCode keys
     *
     * @var array
     */
    public $descriptions = array();

    /**
     * Holds the collection of validators with the validator names as keys
     *
     * @var array
     */
    public $validators = array();

    /**
     * Holds the collection of fieldSettings
     *
     * @var array
     */
    public $fieldSettings = array();

    /**
     * set a field definition name for the given language
     *
     * @param string $name
     * @param string $language
     */
    public function setName( $name, $language )
    {
        $this->names[$language] = $name;
    }

    /**
     * set a  fie definition description for the given language
     *
     * @param string $description
     * @param string $language
     */
    public function setDescription( $description, $language )
    {
        $this->descriptions[$language] = $description;
    }

    /**
     * sets a validator which has to be supported by the field type
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\Validator $validator
     */
    public function setValidator( Validator $validator )
    {
        $this->validators[$validator->name] = $validator;
    }

    /**
     * sets a field settings map supported by the field type
     *
     * @param array $fieldSettings
     */
    public function setFieldSettings( array $fieldSettings )
    {
        $this->fieldSettings = $fieldSettings;
    }
}
