<?php
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 *
 * This class is used for creating a content type group
 * @property-write $names $names[$language] calls setName($language)
 * @property-write $name calls setName() for setting a namein the initial language
 * @property-write $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write $description calls setDescription() for setting a description in an initial language
 *
 */
abstract class ContentTypeGroupCreateStruct extends ValueObject
{
    /**
     * Readable string identifier of a group
     *
     * @var string
     */
    public $identifier;

    /**
     * if set this value overrides the current user as creator
     *
     * @var int
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation
     *
     * @var int (unix timestamp)
     */
    public $created = null;

    /**
     * 5.x only
     * the initial language code
     *
     * @var string
     */
    public $initialLanguageCode;

    /**
     * 5.x only
     * set a content type group name for the given language
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    abstract public function setName( $name, $language = null );

    /**
     * 5.x only
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    abstract public function setDescription( $description, $language = null );
}
