<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Search\FieldType\MultipleStringField class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\Content\Search\FieldType;

use eZ\Publish\SPI\Persistence\Content\Search\FieldType;

/**
 * String document field
 */
class MultipleStringField extends FieldType
{
    /**
     * The type name of the facet. Has to be handled by the solr schema.
     *
     * @var string
     */
    protected $type = 'ez_mstring';
}

