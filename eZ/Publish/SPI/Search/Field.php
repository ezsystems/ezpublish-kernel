<?php
/**
 * File containing the eZ\Publish\SPI\Search\Field class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Search;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * Base class for document fields.
 */
class Field extends ValueObject
{
    /**
     * Name of the document field. Will be used to query this field.
     *
     * @var string
     */
    public $name;

    /**
     * Value of the document field.
     *
     * Might be about anything depending on the type of the document field.
     *
     * @var mixed
     */
    public $value;

    /**
     * Type of the search field
     *
     * @var FieldType
     */
    public $type;

    /**
     * Construct from name and value
     *
     * @param string $name
     * @param mixed $value
     * @param FieldType $type
     */
    public function __construct( $name, $value, FieldType $type )
    {
        $this->name  = $name;
        $this->value = $value;
        $this->type  = $type;
    }
}

