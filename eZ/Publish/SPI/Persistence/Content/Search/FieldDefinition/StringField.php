<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Search\FieldDefinition\StringField class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\Content\Search\FieldDefinition;

use eZ\Publish\SPI\Persistence\Content\Search\FieldDefinition;

/**
 * String document field
 */
class StringField extends FieldDefinition
{
    /**
     * The type name of the facet. Has to be handled by the solr schema.
     *
     * @var string
     */
    protected $type = 'ez_string';
}

