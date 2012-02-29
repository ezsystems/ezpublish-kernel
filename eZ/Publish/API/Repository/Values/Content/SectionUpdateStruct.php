<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to provide data for updating a section. At least one property has to set.
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
