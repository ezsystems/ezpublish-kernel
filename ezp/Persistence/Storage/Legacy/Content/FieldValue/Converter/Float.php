<?php
/**
 * File containing the Float converter
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Content\FieldType\Float\Value as FloatValue,
    ezp\Content\FieldType\FieldSettings;

class Float implements Converter
{
    const FLOAT_VALIDATOR_FQN = 'ezp\\Content\\FieldType\\Float\\FloatValueValidator';

    const NO_MIN_MAX_VALUE = 0;
    const HAS_MIN_VALUE = 1;
    const HAS_MAX_VALUE = 2;

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataFloat = $value->data->value;
        $storageFieldValue->sortKeyInt = $value->sortKey['sort_key_int'];
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new FloatValue( $value->dataFloat );
        $fieldValue->sortKey = array( 'sort_key_int' => $value->sortKeyInt );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_FQN]['minFloatValue'] ) )
        {
            $storageDef->dataFloat1 = $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_FQN]['minFloatValue'];
        }

        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_FQN]['maxFloatValue'] ) )
        {
            $storageDef->dataFloat2 = $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_FQN]['maxFloatValue'];
        }

        // Defining dataInt4 which holds the validator state (min value/max value/minMax value)
        $storageDef->dataFloat4 = $this->getStorageDefValidatorState( $storageDef->dataFloat1, $storageDef->dataFloat2 );
        $storageDef->dataFloat3 = $fieldDef->defaultValue->data->value;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints;

        if ( $storageDef->dataFloat4 !== self::NO_MIN_MAX_VALUE )
        {
            if ( !empty( $storageDef->dataFloat1 ) )
            {
                $fieldDef->fieldTypeConstraints->validators = array(
                    self::FLOAT_VALIDATOR_FQN => array( 'minFloatValue' => $storageDef->dataFloat1 )
                );
            }

            if ( !empty( $storageDef->dataFloat2 ) )
            {
                $fieldDef->fieldTypeConstraints->validators = array(
                    self::FLOAT_VALIDATOR_FQN => array( 'maxFloatValue' => $storageDef->dataFloat2 )
                );
            }
        }

        $defaultValue = isset( $storageDef->dataFloat3 ) ? $storageDef->dataFloat3 : 0.0;
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultValue' => $defaultValue
            )
        );
        $fieldDef->defaultValue = new FieldValue(
            array( 'data' => new FloatValue( $defaultValue ) )
        );
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
