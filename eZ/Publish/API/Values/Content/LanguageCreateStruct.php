<?php
namespace eZ\Publish\API\Values\Content;

use eZ\Publish\API\Values\ValueObject;

/**
 * This class represents a value for creating a language
 *
 */
class LanguageCreateStruct extends ValueObject
{

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
     * indicates if the langiuage is enabled or not.
     *
     * @var boolean
     */
    public $enabled = true;
}

