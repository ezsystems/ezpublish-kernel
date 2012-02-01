<?php
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating a content type
 *
 * @property-write $names $names[$language] calls setName($language)
 * @property-write $name calls setName() for setting a namein the initial language
 * @property-write $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write $description calls setDescription() for setting a description in an initial language
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
     * If set the initial language is changed to this value
     *
     * @var mixed
     */
    public $initialLanguageId;

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
     * @var int (unix timestamp)
     */
    public $modified = null;

    /**
     * set a content type name for the given language
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    public abstract function setName( $name, $language = null );

    /**
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    public abstract function setDescription( $description, $language = null );
}
