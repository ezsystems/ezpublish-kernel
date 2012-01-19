<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class represents a language in the repository.
 *
 */
class Language extends ValueObject {
    
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
     * indicates if the langiuage is enabled or not.
     * 
     * @var boolean
     */
    public $enabled;
}