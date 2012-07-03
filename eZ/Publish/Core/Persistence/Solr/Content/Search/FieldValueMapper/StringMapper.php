<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper;

use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldValueMapper,
    eZ\Publish\SPI\Persistence\Content\Search\DocumentField;

/**
 * Maps raw document field values to something Solr can index.
 */
class StringMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped
     *
     * @param DocumentField $field
     * @return void
     */
    public function canMap( DocumentField $field )
    {
        return
            $field instanceof DocumentField\StringField ||
            $field instanceof DocumentField\TextField ||
            $field instanceof DocumentField\HtmlField;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param DocumentField $field
     * @return void
     */
    public function map( DocumentField $field )
    {
        // Remove non-printables
        return preg_replace( '/[\x00-\x09\x0B\x0C\x1E\x1F]/', '', $field->value );
    }
}

