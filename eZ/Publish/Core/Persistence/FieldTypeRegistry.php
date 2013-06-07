<?php
/**
 * File containing the FieldTypeRegistry class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence;

use eZ\Publish\SPI\FieldType\FieldType as FieldTypeInterface;
use eZ\Publish\Core\Persistence\FieldType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use ArrayObject;
use RuntimeException;

/**
 * Registry for field types available to storage engines.
 */
class FieldTypeRegistry
{
    /**
     * Map of FieldTypes where key is field type identifier and value is FieldType object complying
     * to {@link \eZ\Publish\SPI\FieldType\FieldType} interface or callable callback to generate one.
     *
     * @var mixed
     */
    protected $coreFieldTypeMap = array();

    /**
     * Map of FieldTypes where key is field type identifier and value is FieldType object.
     *
     * @var \eZ\Publish\SPI\Persistence\FieldType[]
     */
    protected $fieldTypeMap = array();

    /**
     * Creates FieldType registry.
     *
     * In $fieldTypeMap a mapping of field type identifier to object / callable is
     * expected, in case of callable factory it should return the FieldType object.
     * The FieldType object must comply to the {@link \eZ\Publish\SPI\FieldType\FieldType} interface.
     *
     * @param array|\ArrayObject $fieldTypeMap A map where key is field type identifier and value is
     *              a callable factory to get FieldType OR FieldType object.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType If $fieldTypeMap is of wrong type
     */
    public function __construct( $fieldTypeMap )
    {
        if ( !$fieldTypeMap instanceof ArrayObject && !is_array( $fieldTypeMap ) )
        {
            throw new InvalidArgumentType( '\$fieldTypeMap', 'array|\ArrayObject', $fieldTypeMap );
        }

        $this->coreFieldTypeMap = $fieldTypeMap;
    }

    /**
     * Returns the FieldType object for given $identifier.
     *
     * @param string $identifier
     *
     * @throws \RuntimeException If field type for given $identifier is not found.
     * @throws \RuntimeException If field type for given $identifier is not instance or callable.
     *
     * @return \eZ\Publish\SPI\Persistence\FieldType
     */
    public function getFieldType( $identifier )
    {
        if ( !isset( $this->fieldTypeMap[$identifier] ) )
        {
            $this->fieldTypeMap[$identifier] = new FieldType( $this->getCoreFieldType( $identifier ) );
        }

        return $this->fieldTypeMap[$identifier];
    }

    /**
     * Register $fieldType with $identifier.
     *
     * For $fieldType an object / callable is expected, in case of callable factory it should return
     * the FieldType object.
     * The FieldType object must comply to the {@link \eZ\Publish\SPI\FieldType\FieldType} interface.
     *
     * @param $identifier
     * @param callable|\eZ\Publish\SPI\FieldType\FieldType $fieldType Callable or FieldType instance.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType If $fieldTypeMap is of wrong type
     *
     * @return void
     */
    public function register( $identifier, $fieldType )
    {
        if ( !$fieldType instanceof FieldTypeInterface && !is_callable( $fieldType ) )
        {
            throw new InvalidArgumentType( '\$fieldType', 'callable|\eZ\Publish\SPI\FieldType\FieldType', $fieldType );
        }

        $this->coreFieldTypeMap[$identifier] = $fieldType;
    }

    /**
     * Instantiates a FieldType object.
     *
     * @throws \RuntimeException If field type for given $identifier is not found.
     * @throws \RuntimeException If field type for given $identifier is not instance or callable.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\FieldType\FieldType
     */
    protected function getCoreFieldType( $identifier )
    {
        if ( !isset( $this->coreFieldTypeMap[$identifier] ) )
        {
            // Get array copy in case of ArrayObject
            if ( $this->coreFieldTypeMap instanceof ArrayObject )
                $array = $this->coreFieldTypeMap->getArrayCopy();
            else
                $array = $this->coreFieldTypeMap;

            throw new RuntimeException(
                "Provided \$identifier is unknown: '{$identifier}', have: "
                . var_export( array_keys( $array ), true )
            );
        }

        $fieldType = $this->coreFieldTypeMap[$identifier];
        if ( !$fieldType instanceof FieldTypeInterface )
        {
            if ( !is_callable( $fieldType ) )
            {
                throw new RuntimeException( "FieldType '$identifier' is not callable or instance" );
            }

            /** @var $fieldType \Closure */
            $fieldType = $fieldType();
        }

        return $fieldType;
    }
}
