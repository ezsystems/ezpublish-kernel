<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Search\FieldType class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\Content\Search;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Base class for document field definitions
 */
abstract class FieldType extends ValueObject
{
    /**
     * Name of the document field. Will be used to query this field.
     *
     * @var string
     */
    public $name;

    /**
     * The type name of the facet. Has to be handled by the solr schema.
     *
     * @var string
     */
    protected $type;

    /**
     * Whether highlighting should be performed for this field on result documents
     *
     * @var boolean
     */
    public $highlight = false;

    /**
     * The importance of that field ( boost factor) )
     *
     * @var int
     */
    public $boost = 1;

    /**
     * Whether the field supports multiple values
     *
     * @var boolean
     */
    public $multiValue = false;

    /**
     * Whether the field should be a part of the resulting document
     *
     * @var boolean
     */
    public $inResult = true;
}

