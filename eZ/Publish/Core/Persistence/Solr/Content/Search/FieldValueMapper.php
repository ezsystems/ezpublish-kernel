<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\SPI\Persistence\Content\Search\Field;

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
     * @return void
     */
    abstract public function canMap( Field $field );

    /**
     * Map field value to a proper Solr representation
     *
     * @param Field $field
     *
     * @return void
     */
    abstract public function map( Field $field );
}

