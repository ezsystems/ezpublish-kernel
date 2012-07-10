<?php
/**
 * File containing the NullStorage class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\FieldType\FieldStorage,
    eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Description of NullStorage
 */
class NullStorage implements FieldStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()
     */
    public function storeFieldData( Field $field, array $context )
    {
        return;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::getFieldData()
     */
    public function getFieldData( Field $field, array $context )
    {
        return;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::deleteFieldData()
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
        return true;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::hasFieldData()
     * @return bool
     */
    public function hasFieldData()
    {
        return false;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::copyFieldData()
     */
    public function copyFieldData( Field $field, array $context )
    {
        return;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::getIndexData()
     */
    public function getIndexData( Field $field, array $context )
    {
        return false;
    }
}
