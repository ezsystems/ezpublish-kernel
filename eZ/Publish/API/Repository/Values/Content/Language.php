<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a language in the repository.
 *
 * @property-read int $id the language id
 * @property-read string $languageCode the language code in
 * @property-read string $name human readable name of the language
 * @property-read boolean $enabled indicates if the language is enabled or not.
 */
class Language extends ValueObject
{
    /**
     * The language id (auto generated)
     */
    public $id;

    /**
     * the languageCode code
     *
     * @var string
     */
    public $languageCode;

    /**
     * Human readable name of the language
     *
     * @var string
     */
    public $name;

    /**
     * indicates if the language is enabled or not.
     *
     * @var boolean
     */
    public $enabled;
}
