<?php
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating a content type group
 * 
 * @property $names the collection of names with languageCode keys. 
 *           the calls <code>$ctcs->names[$language] = "abc"</code> and <code>$ctcs->setName("abc",$language)</code> are equivalent
 * @property $name the name of the content type in the main language
 *           the calls  <code>$ctcs->name = "abc"</code> and <code>$ctcs->setName("abc")</code> are equivalent calls
 * @property $descriptions the collection of descriptions with languageCode keys. 
 *           the calls <code>$ctcs->descriptions[$language] = "abc"</code> and <code>$ctcs->setDescription("abc",$language)</code> are equivalent
 * @property $name the name of the content type in the main language
 *           the calls  <code>$ctcs->name = "abc"</code> and <code>$ctcs->setName("abc")</code> are equivalent calls
 */
abstract class ContentTypeGroupUpdateStruct extends ValueObject
{

    /**
     * Readable string identifier of a group
     *
     * @var string
     */
    public $identifier;

    /**
     * if set this value overrides the current user as modifier
     *
     * @var int
     */
    public $modifierId = null;

    /**
     * If set this value overrides the current time for modified
     *
     * @var DateTime
     */
    public $modificationDate = null;

    /**
     * if set the main language code is changed to this value
     * 
     * @since 5.0
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * set a content type group name for the given language
     * 
     * @since 5.0
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    abstract public function setName( $name, $language = null );

    /**
     * set a content type description for the given language
     * 
     * @since 5.0
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    abstract public function setDescription( $description, $language = null );
}
