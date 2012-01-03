<?php
/**
 * File containing the NullStorage class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Fields\Storage,
    ezp\Persistence\Content\Field;

/**
 * Description of NullStorage
 */
class NullStorage implements Storage
{
    /**
     * @see \ezp\Persistence\Fields\Storage::storeFieldData()
     */
    public function storeFieldData( Field $field, array $context )
    {
        return;
    }

    /**
     * @see \ezp\Persistence\Fields\Storage::getFieldData()
     */
    public function getFieldData( Field $field, array $context )
    {
        return;
    }

    /**
     * @see \ezp\Persistence\Fields\Storage::deleteFieldData()
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
        return true;
    }

    /**
     * @see \ezp\Persistence\Fields\Storage::hasFieldData()
     * @return bool
     */
    public function hasFieldData()
    {
        return false;
    }

    /**
     * @see \ezp\Persistence\Fields\Storage::copyFieldData()
     */
    public function copyFieldData( Field $field, array $context )
    {
        return;
    }

    /**
     * @see \ezp\Persistence\Fields\Storage::getIndexData()
     */
    public function getIndexData( Field $field, array $context )
    {
        return false;
    }
}
