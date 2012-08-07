<?php
/**
 * File containing the eZ\Publish\API\Repository\FieldType class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\Core\Repository\Values\ContentType;
use eZ\Publish\API\Repository\FieldType as FieldTypeInterface;

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
    public function __construct( $fieldType )
    {
        $this->internalFieldType = $fieldType;
    }

    /**
     * Return the field type identifier for this field type
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
     * @return bool
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
    public function getDefaultDefaultValue()
    {
        return $this->internalFieldType->getDefaultDefaultValue();
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
}
