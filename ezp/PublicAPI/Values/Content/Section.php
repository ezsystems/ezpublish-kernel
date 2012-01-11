<?php
namespace ezp\PublicAPI\Values\Content;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a section
 */
class Section extends ValueObject
{
    /**
     * Id of the section
     *
     * @var int
     */
    public $id;

    /**
     * Unique identifier of the section
     *
     * @var string
     */
    public $identifier;

    /**
     * Name of the section
     *
     * @var string
     */
    public $name;
}
?>
