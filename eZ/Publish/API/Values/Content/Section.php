<?php
namespace eZ\Publish\API\Values\Content;

use eZ\Publish\API\Values\ValueObject;

/**
 * This class represents a section
 * 
 * @property-read int $id the id of the section
 * @property-read string $identifier the identifier of the section
 * @property-read string $name human readable name of the section
 */
class Section extends ValueObject
{
    /**
     * Id of the section
     *
     * @var int
     */
    protected $id;

    /**
     * Unique identifier of the section
     *
     * @var string
     */
    protected $identifier;

    /**
     * Name of the section
     *
     * @var string
     */
    protected $name;
}
