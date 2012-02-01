<?php
namespace eZ\Publish\API\Values\Content;

use eZ\Publish\API\Values\ValueObject;

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

