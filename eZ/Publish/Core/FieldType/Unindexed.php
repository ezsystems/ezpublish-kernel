<?php
/**
 * File containing the UserStorage class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\FieldType\Indexable;

/**
 * Indexable definition for string field type
 */
class Unindexed implements Indexable
{
    /**
     * Get index data for field for search backend
     *
     * @param Field $field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Field[]
     */
    public function getIndexData( Field $field )
    {
        return array();
    }

    /**
     * Get index field types for search backend
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\FieldType[]
     */
    public function getIndexDefinition()
    {
        return array();
    }
}

