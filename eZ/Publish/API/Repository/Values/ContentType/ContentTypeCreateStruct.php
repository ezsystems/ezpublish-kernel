<?php
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreate;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * this clss is used for creating content types
 *
 * @property $names the collection of names with languageCode keys. 
 *           the calls <code>$ctcs->names[$language] = "abc"</code> and <code>$ctcs->setName("abc",$language)</code> are equivalent
 * @property $name the name of the content type in the main language
 *           the calls  <code>$ctcs->name = "abc"</code> and <code>$ctcs->setName("abc")</code> are equivalent calls
 * @property $descriptions the collection of descriptions with languageCode keys. 
 *           the calls <code>$ctcs->descriptions[$language] = "abc"</code> and <code>$ctcs->setDescription("abc",$language)</code> are equivalent
 * @property $name the name of the content type in the main language
 *           the calls  <code>$ctcs->name = "abc"</code> and <code>$ctcs->setName("abc")</code> are equivalent calls
 * @property $fieldDefinitions the collection of field definitions
 */
abstract class ContentTypeCreateStruct extends ValueObject
{
    /**
     * String identifier of a type
     *
     * @var string
     */
    public $identifier;

    /**
     * Main language Code.
     *
     * @var string
     */
    public $mainLanguageCode;

    /**
     * The renote id
     *
     * @var string
     */
    public $remoteId;

    /**
     * URL alias schema
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * Name schema
     *
     * @var string
     */
    public $nameSchema;

    /**
     * Determines if the type is a container
     *
     * @var boolean
     */
    public $isContainer = false;

    /**
     * Specifies which property the child locations should be sorted on by default when created
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $defaultSortField = Location::SORT_FIELD_PUBLISHED;

    /**
     * Specifies whether the sort order should be ascending or descending by default when created
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $defaultSortOrder = Location::SORT_ORDER_DESC;

    /**
     * if an instance of acontent type is created the always available flag is set
     * by default this this value.
     *
     * @var boolean
     */
    public $defaultAlwaysAvailable = true;

    /**
     * set a content type name for the given language
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    abstract public function setName( $name, $language = null );

    /**
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    abstract public function setDescription( $description, $language = null );

    /**
     * adds a new field definition
     *
     * @param FieldDefinitionCreate $fieldDef
     */
    abstract public function addFieldDefinition( /*FieldDefinitionCreate*/ $fieldDef );

    /**
     * if set this value overrides the current user as creator
     * @var int
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation
     * @var DateTime
     */
    public $creationDate = null;
}
