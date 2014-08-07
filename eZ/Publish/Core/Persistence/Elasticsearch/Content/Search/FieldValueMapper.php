<?php
/**
 * File containing the FieldValueMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search;

use eZ\Publish\SPI\Persistence\Content\Search\Field;

/**
 * Maps raw document field values to something Elasticsearch can index.
 */
abstract class FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return boolean
     */
    abstract public function canMap( Field $field );

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Search\Field $field
     *
     * @return mixed
     */
    abstract public function map( Field $field );
}

