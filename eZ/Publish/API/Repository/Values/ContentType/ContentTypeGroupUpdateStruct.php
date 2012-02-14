<?php
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating a content type group
 * 
 * @property-write $names $names[$language] calls setName($language)
 * @property-write $name calls setName() for setting a namein the initial language
 * @property-write $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write $description calls setDescription() for setting a description in an initial language
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
