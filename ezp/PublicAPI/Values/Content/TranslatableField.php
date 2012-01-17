<?php
namespace ezp\PublicAPI\Values\Content;

/**
 *
 * This class represents a translatable field of a content object
 * The translatable field is a field in a default language and has additionally a map of translations
 */
abstract class TranslatableField extends Field implements ArrayAccess {
    /**
     *
     *
     * @var array anguage map of @link Field}
     */
    protected $translations;

}
