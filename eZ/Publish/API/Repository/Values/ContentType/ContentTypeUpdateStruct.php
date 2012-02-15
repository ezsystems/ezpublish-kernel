<?php
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating a content type
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
abstract class ContentTypeUpdateStruct extends ValueObject
{
    /**
     * If set the identifier of a type is changed to this value
     *
     * @var string
     */
    public $identifier;

    /**
     * If set the remote ID is changed to this value
     *
     * @var string
     */
    public $remoteId;

    /**
     * If set the URL alias schema is changed to this value
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * If set the name schema is changed to this value
     *
     * @var string
     */
    public $nameSchema;

    /**
     * If set the container fllag is set to this value
     *
     * @var boolean
     */
    public $isContainer;

    /**
     * If set the main language is changed to this value
     *
     * @var mixed
     */
    public $mainLanguageCode;

    /**
     * If set the default sort field is changed to this value
     *
     * @var mixed
     */
    public $defaultSortField;

    /**
     * If set the default sort order is set to this value
     *
     * @var mixed
     */
    public $defaultSortOrder;

    /**
     * If set the default always available flag is set to this value
     *
     * @var boolean
     */
    public $defaultAlwaysAvailable;

    /**
     * If set this value overrides the current user as creator
     *
     * @var int
     */
    public $modifierId = null;

    /**
     * If set this value overrides the current time for creation
     *
     * @var DateTime
     */
    public $modificationDate = null;

    /**
     * set a content type name for the given language
     *
     * @param string $name
     * @param string $language if not given the main Language is used as default
     */
    abstract public function setName( $name, $language = null );

    /**
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the main Language is used as default
     */
    abstract public function setDescription( $description, $language = null );
}
