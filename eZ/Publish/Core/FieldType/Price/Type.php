<?php
/**
 * File containing the Price Type class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Price;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;

class Type extends FieldType
{
    public function getFieldTypeIdentifier()
    {
        return 'ezprice';
    }

    public function getName( SPIValue $value )
    {
    }

    public function getEmptyValue()
    {
        return new Value;
    }

    public function isEmptyValue( SPIValue $value )
    {
    }

    protected function createValueFromInput( $inputValue )
    {
    }

    protected function checkValueStructure( BaseValue $value )
    {
    }

    protected function getSortInfo( BaseValue $value )
    {
    }

    public function fromHash( $hash )
    {
        return new Value( $hash );
    }

    public function toHash( SPIValue $value )
    {
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\Price\Value
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return new Value( $fieldValue->externalData );
    }
} 