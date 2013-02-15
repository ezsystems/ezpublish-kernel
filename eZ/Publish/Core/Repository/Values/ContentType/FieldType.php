<?php
/**
 * File containing the eZ\Publish\API\Repository\FieldType class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\FieldType as FieldTypeInterface;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldTypeInterface;

/**
 * This class represents a FieldType available to Public API users
 *
 * @package eZ\Publish\Core\Repository
 * @see eZ\Publish\API\Repository\FieldType
 */
class FieldType implements FieldTypeInterface
{
    /**
     * Holds internal FieldType object
     *
     * @var \eZ\Publish\Core\FieldType\FieldType
     */
    protected $internalFieldType;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     */
    public function __construct( SPIFieldTypeInterface $fieldType )
    {
        $this->internalFieldType = $fieldType;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return $this->internalFieldType->getFieldTypeIdentifier();
    }

    public function getName( $value )
    {
        return $this->internalFieldType->getName( $value );
    }

    /**
     * Returns a schema for the settings expected by the FieldType
     *
     * Returns an arbitrary value, representing a schema for the settings of
     * the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementer to define and document a schema for
     * the return value and document it. In addition, it is necessary that all
     * consumers of this interface (e.g. Public API, REST API, GUIs, ...)
     * provide plugin mechanisms to hook adapters for the specific FieldType
     * into. These adapters then need to be either shipped with the FieldType
     * or need to be implemented by a third party. If there is no adapter
     * available for a specific FieldType, it will not be usable with the
     * consumer.
     *
     * @return mixed
     */
    public function getSettingsSchema()
    {
        return $this->internalFieldType->getSettingsSchema();
    }

    /**
     * Returns a schema for the validator configuration expected by the FieldType
     *
     * Returns an arbitrary value, representing a schema for the validator
     * configuration of the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementer to define and document a schema for
     * the return value and document it. In addition, it is necessary that all
     * consumers of this interface (e.g. Public API, REST API, GUIs, ...)
     * provide plugin mechanisms to hook adapters for the specific FieldType
     * into. These adapters then need to be either shipped with the FieldType
     * or need to be implemented by a third party. If there is no adapter
     * available for a specific FieldType, it will not be usable with the
     * consumer.
     *
     * Best practice:
     *
     * It is considered best practice to return a hash map, which contains
     * rudimentary settings structures, like e.g. for the "ezstring" FieldType
     *
     * <code>
     *  array(
     *      'stringLength' => array(
     *          'minStringLength' => array(
     *              'type'    => 'int',
     *              'default' => 0,
     *          ),
     *          'maxStringLength' => array(
     *              'type'    => 'int'
     *              'default' => null,
     *          )
     *      ),
     *  );
     * </code>
     *
     * @return mixed
     */
    public function getValidatorConfigurationSchema()
    {
        return $this->internalFieldType->getValidatorConfigurationSchema();
    }

    /**
     * Indicates if the field type supports indexing and sort keys for searching
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return $this->internalFieldType->isSearchable();
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return mixed
     */
    public function getEmptyValue()
    {
        return $this->internalFieldType->getEmptyValue();
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function fromHash( $hash )
    {
        return $this->internalFieldType->fromHash( $hash );
    }

    /**
     * Converts a Value to a hash
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $this->internalFieldType->toHash( $value );
    }

    /**
     * Converts the given $fieldSettings to a simple hash format
     *
     * @param mixed $fieldSettings
     *
     * @return array|hash|scalar|null
     */
    public function fieldSettingsToHash( $fieldSettings )
    {
        return $this->internalFieldType->fieldSettingsToHash( $fieldSettings );
    }

    /**
     * Converts the given $fieldSettingsHash to field settings of the type
     *
     * This is the reverse operation of {@link fieldSettingsToHash()}.
     *
     * @param array|hash|scalar|null $fieldSettingsHash
     *
     * @return mixed
     */
    public function fieldSettingsFromHash( $fieldSettingsHash )
    {
        return $this->internalFieldType->fieldSettingsFromHash( $fieldSettingsHash );
    }

    /**
     * Converts the given $validatorConfiguration to a simple hash format
     *
     * @param mixed $validatorConfiguration
     *
     * @return array|hash|scalar|null
     */
    public function validatorConfigurationToHash( $validatorConfiguration )
    {
        return $this->internalFieldType->validatorConfigurationToHash( $validatorConfiguration );
    }

    /**
     * Converts the given $validatorConfigurationHash to a validator
     * configuration of the type
     *
     * @param array|hash|scalar|null $validatorConfigurationHash
     *
     * @return mixed
     */
    public function validatorConfigurationFromHash( $validatorConfigurationHash )
    {
        return $this->internalFieldType->validatorConfigurationFromHash( $validatorConfigurationHash );
    }
}
