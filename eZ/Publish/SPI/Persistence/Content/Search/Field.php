<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Search\Field class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\Content\Search;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Base class for document fields
 */
class Field extends ValueObject
{
    /**
     * Name of the document field. Will be used to query this field.
     *
     * @var string
     */
    protected $name;

    /**
     * Value of the document field.
     *
     * Might be about anything depending on the type of the document field.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Type of the search field
     *
     * @var FieldType
     */
    protected $type;

    /**
     * Construct from name and value
     *
     * @param string $name
     * @param mixed $value
     * @param FieldType $type
     *
     * @return void
     */
    public function __construct( $name, $value, FieldType $type )
    {
        $this->name  = $name;
        $this->value = $value;
        $this->type  = $type;
    }
}

