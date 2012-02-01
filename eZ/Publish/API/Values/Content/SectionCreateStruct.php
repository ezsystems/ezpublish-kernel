<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a section
 */
class SectionCreateStruct extends ValueObject
{

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

