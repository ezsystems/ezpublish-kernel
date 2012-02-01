<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a field of a content object
 * 
 * @property-read int $id an internal id of the field
 * @property-read string $fieldDefIdentifier the field definition identifier
 * @property-read $value the value of the field
 * @property-read string $languageCode the language code of the field
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
    protected $id;

    /**
     *
     * The field definition identifier
     *
     * @var string
     */
    protected $fieldDefIdentifier;

    /**
     * a field type value or a value type which can be converted by the corresponding field type
     *
     * @var mixed
     */
    protected $value;

    /**
     * the language code
     *
     * @var string
     */
    protected $languageCode;
}
