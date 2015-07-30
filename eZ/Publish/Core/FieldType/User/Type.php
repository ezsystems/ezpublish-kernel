<?php

/**
 * File containing the User class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\User;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * The User field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezuser';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string)$value->login;
    }

    /**
     * Indicates if the field definition of this type can appear only once in the same ContentType.
     *
     * @return bool
     */
    public function isSingular()
    {
        return true;
    }

    /**
     * Indicates if the field definition of this type can be added to a ContentType with Content instances.
     *
     * @return bool
     */
    public function onlyEmptyInstance()
    {
        return true;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\User\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param array|\eZ\Publish\Core\FieldType\User\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\User\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        // Does nothing
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     */
    protected function getSortInfo(BaseValue $value)
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\User\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return new Value($hash);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return (array)$value;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * In this method the field type puts the data which is stored in the field of content in the repository
     * into the property FieldValue::data. The format of $data is a primitive, an array (map) or an object, which
     * is then canonically converted to e.g. json/xml structures by future storage engines without
     * further conversions. For mapping the $data to the legacy database an appropriate Converter
     * (implementing eZ\Publish\Core\Persistence\Legacy\FieldValue\Converter) has implemented for the field
     * type. Note: $data should only hold data which is actually stored in the field. It must not
     * hold data which is stored externally.
     *
     * The $externalData property in the FieldValue is used for storing data externally by the
     * FieldStorage interface method storeFieldData.
     *
     * The FieldValuer::sortKey is build by the field type for using by sort operations.
     *
     * @see \eZ\Publish\SPI\Persistence\Content\FieldValue
     *
     * @param \eZ\Publish\Core\FieldType\User\Value $value The value of the field type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue the value processed by the storage engine
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new FieldValue(
            array(
                'data' => null,
                'externalData' => $this->toHash($value),
                'sortKey' => null,
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\User\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        return $this->acceptValue($fieldValue->externalData);
    }
}
