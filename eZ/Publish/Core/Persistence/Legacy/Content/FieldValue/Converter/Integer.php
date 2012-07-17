<?php
/**
 * File containing the Integer converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\FieldType\Integer\Value as IntegerValue,
    eZ\Publish\Core\FieldType\FieldSettings;

class Integer implements Converter
{
    const FLOAT_VALIDATOR_IDENTIFIER = "IntegerValueValidator";

    const NO_MIN_MAX_VALUE = 0;
    const HAS_MIN_VALUE = 1;
    const HAS_MAX_VALUE = 2;

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataInt = $value->data;
        $storageFieldValue->sortKeyInt = $value->sortKey['sort_key_int'];
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $value->dataInt;
        $fieldValue->sortKey = array( 'sort_key_int' => $value->sortKeyInt );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['minIntegerValue'] ) )
        {
            $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['minIntegerValue'];
        }

        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['maxIntegerValue'] ) )
        {
            $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['maxIntegerValue'];
        }

        // Defining dataInt4 which holds the validator state (min value/max value/minMax value)
        $storageDef->dataInt4 = $this->getStorageDefValidatorState( $storageDef->dataInt1, $storageDef->dataInt2 );
        $storageDef->dataInt3 = $fieldDef->defaultValue->data;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        if ( $storageDef->dataInt4 !== self::NO_MIN_MAX_VALUE )
        {
            $fieldDef->fieldTypeConstraints->validators = array(
                self::FLOAT_VALIDATOR_IDENTIFIER => array( 'minIntegerValue' => false, 'maxIntegerValue' => false )
            );

            if ( !empty( $storageDef->dataInt1 ) )
            {
                $fieldDef->fieldTypeConstraints
                         ->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['minIntegerValue'] = $storageDef->dataInt1;
            }

            if ( !empty( $storageDef->dataInt2 ) )
            {
                $fieldDef->fieldTypeConstraints
                         ->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['maxIntegerValue'] = $storageDef->dataInt2;
            }
        }

        $fieldDef->defaultValue->data = isset( $storageDef->dataInt3 ) ? $storageDef->dataInt3 : 0;
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return 'sort_key_int';
    }

    /**
     * Returns validator state for storage definition.
     * Validator state is a bitfield value composed of:
     *   - {@link self::NO_MIN_MAX_VALUE}
     *   - {@link self::HAS_MAX_VALUE}
     *   - {@link self::HAS_MIN_VALUE}
     *
     * @param int|null $minValue Minimum int value, or null if not set
     * @param int|null $maxValue Maximum int value, or null if not set
     * @return int
     */
    private function getStorageDefValidatorState( $minValue, $maxValue )
    {
        $state = self::NO_MIN_MAX_VALUE;

        if ( $minValue !== null )
            $state += self::HAS_MIN_VALUE;

        if ( $maxValue !== null )
            $state += self::HAS_MAX_VALUE;

        return $state;
    }
}
