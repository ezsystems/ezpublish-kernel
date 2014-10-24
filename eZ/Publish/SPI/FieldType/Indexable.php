<?php
/**
 * File containing the FieldType Indexable interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType;

use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * The field type interface which all field types have to implement to be
 * indexable by search backends.
 *
 * @package eZ\Publish\SPI\FieldType
 */
interface Indexable
{
    /**
     * Get index data for field for search backend
     *
     * @param Field $field
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData( Field $field );

    /**
     * Get index field types for search backend
     *
     * @return \eZ\Publish\SPI\Search\FieldType[]
     */
    public function getIndexDefinition();
}

