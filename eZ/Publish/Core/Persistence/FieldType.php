<?php
/**
 * File containing the eZ\Publish\Core\Persistence\FieldType class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence;

use eZ\Publish\SPI\Persistence\FieldType as FieldTypeInterface;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;

/**
 * This class represents a FieldType available to SPI users.
 *
 * @see \eZ\Publish\SPI\FieldType\FieldType
 * @package \eZ\Publish\Core\Persistence\FieldType
 */
class FieldType implements FieldTypeInterface
{
    /**
     * Holds internal FieldType object.
     *
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    protected $internalFieldType;

    /**
     * Creates a new FieldType object.
     *
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     */
    public function __construct( SPIFieldType $fieldType )
    {
        $this->internalFieldType = $fieldType;
    }

    /**
     * Returns the empty value for the field type that can be processed by the storage engine.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getEmptyValue()
    {
        return $this->internalFieldType->toPersistenceValue(
            $this->internalFieldType->getEmptyValue()
        );
    }
}
