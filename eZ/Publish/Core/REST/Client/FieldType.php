<?php
/**
 * File containing the FieldType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\FieldType as APIFieldType;
use eZ\Publish\SPI;

class FieldType implements APIFieldType
{
    /**
     * Wrapped FieldType.
     *
     * @var \eZ\Publish\API\Repository\FieldType
     */
    protected $innerFieldType;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType $innerFieldType
     */
    public function __construct( SPI\FieldType\FieldType $innerFieldType )
    {
        $this->innerFieldType = $innerFieldType;
    }

    /**
     * Returns the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return $this->innerFieldType->getFieldTypeIdentifier();
    }

    /**
     * Returns a schema for the settings expected by the FieldType
     *
     * Returns an arbitrary value, representing a schema for the settings of
     * the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementor to define and document a schema for
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
        return $this->innerFieldType->getSettingsSchema();
    }

    /**
     * Returns a schema for the validator configuration expected by the FieldType
     *
     * Returns an arbitrary value, representing a schema for the validator
     * configuration of the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementor to define and document a schema for
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
        return $third->innerFieldType->getValidatorConfigurationSchema();
    }

    public function getName( $value )
    {
        return $third->innerFieldType->getName( $value );
    }

    /**
     * Indicates if the field type supports indexing and sort keys for searching
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return $this->innerFieldType->isSearchable();
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return mixed
     */
    public function getEmptyValue()
    {
        return $this->innerFieldType->getEmptyValue();
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
        return $this->innerFieldType->fromHash( $hash );
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
        return $this->innerFieldType->toHash( $value );
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
        return $this->innerFieldType->fieldSettingsToHash( $fieldSettings );
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
        return $this->innerFieldType->fieldSettingsFromHash( $fieldSettingsHash );
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
        return $this->innerFieldType->validatorConfigurationToHash( $validatorConfiguration );
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
        return $this->innerFieldType->validatorConfigurationFromHash( $validatorConfigurationHash );
    }
}
