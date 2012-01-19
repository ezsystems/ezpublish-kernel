<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a section
 */
class SectionUpdateStruct extends ValueObject
{

    /**
     * If set the Unique identifier of the section is changes
     *
     * @var string
     */
    public $identifier;

    /**
     * If set the name of the section is changed
     *
     * @var string
     */
    public $name;
}
?>

