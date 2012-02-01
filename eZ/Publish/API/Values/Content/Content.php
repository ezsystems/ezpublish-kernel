<?php
namespace eZ\Publish\API\Values\Content;

use eZ\Publish\API\Values\ValueObject;

use eZ\Publish\API\Values\Content\VersionInfo;

/**
 *
 * this class represents a content object in a specific version
 *
 * @property-read ContentInfo $contentInfo convenience getter for $versionInfo->contentInfo
 * @property-read ContentType $contentType convenience getter for $contentInfo->contentType
 * @property-read int $contentId convenience getter for retrieving the contentId: $versionInfo->content->contentId
 * @property-read VersionInfo $versionInfo calls getVersionInfo()
 * @property-read array $fields access fields
 * @property-read array $relations calls getRelations()
 *
 */
abstract class Content extends ValueObject
{
    /**
     * returns the VersionInfo for this version
     *
     * @return VersionInfo
     */
    public abstract function getVersionInfo();

    /**
     * returns a field value for the given value
     * $version->fields[$fieldDefId][$languageCode] is an equivalent call
     * if no language is given on a translatable field this method returns
     * the value of the initial language of the version if present, otherwise null.
     * On non translatable fields this method ignores the languageCode parameter.
     *
     * @param string $fieldDefIdentifer
     * @param string $languageCode
     *
     * @return mixed a primitive type or a field type Value object depending on the field type.
     */
    public abstract function getFieldValue( $fieldDefIdentifer, $languageCode = null );

    /**
     * returns the outgoing relations
     *
     * @return array an array of {@link Relation}
     */
    public abstract function getRelations();

    /**
     * This method returns the complete fields collection
     *
     * @return array an array of {@link Field}
     */
    public abstract function getFields();

    /**
     * This method returns the fields for a given language and non translatable fields
     * 
     * If note set the initilaLanguage of the content version is used.
     * 
     * @param string $languageCode
     * 
     * @return array an array of {@link Field} with field identifier as keys
     */
    public abstract function getFieldsByLanguage( $languageCode = null );
}
