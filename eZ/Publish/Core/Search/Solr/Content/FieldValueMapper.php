<?php
/**
 * File containing the FieldValueMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content;

use eZ\Publish\SPI\Search\Field;

/**
 * Maps raw document field values to something Solr can index.
 */
abstract class FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param Field $field
     *
     * @return boolean
     */
    abstract public function canMap( Field $field );

    /**
     * Map field value to a proper Solr representation
     *
     * @param Field $field
     *
     * @return mixed|null Returns null on empty value
     */
    abstract public function map( Field $field );
}

