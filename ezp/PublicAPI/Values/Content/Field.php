<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

/**
 *
 * This class represents a field of a content object
 */
class Field extends ValueObject
{
    /**
     * The field id
     *
     * @todo may be not needed
     *
     * @var int
     */
    public $id;

    /**
     *
     * The field definition identifier
     *
     * @var string
     */
    public $fieldDefIdentifier;

    /**
     * a field type value or a value type which can be converted by the corresponding field type
     *
     * @var mixed
     */
    public $value;

    /**
     * the language code
     *
     * @var string
     */
    public $languageCode;
}
