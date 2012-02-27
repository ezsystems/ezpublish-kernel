<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 *
 * this class represents a content object in a specific version
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo convenience getter for $versionInfo->contentInfo
 * @property-read \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType convenience getter for $contentInfo->contentType
 * @property-read mixed $contentId convenience getter for retrieving the contentId: $versionInfo->content->contentId
 * @property-read \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo calls getVersionInfo()
 * @property-read array $fields access fields
 * @property-read array $relations calls getRelations()
 *
 */
abstract class Content extends ValueObject
{
    /**
     * returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    abstract public function getVersionInfo();

    /**
     * returns a field value for the given value
     * $version->fields[$fieldDefId][$languageCode] is an equivalent call
     * if no language is given on a translatable field this method returns
     * the value of the initial language of the version if present, otherwise null.
     * On non translatable fields this method ignores the languageCode parameter.
     *
     * @param string $fieldDefIdentifier
     * @param string $languageCode
     *
     * @return mixed a primitive type or a field type Value object depending on the field type.
     */
    abstract public function getFieldValue( $fieldDefIdentifier, $languageCode = null );

    /**
     * returns the outgoing relations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[] An array of {@link Relation}
     */
    abstract public function getRelations();

    /**
     * This method returns the complete fields collection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[] An array of {@link Field}
     */
    abstract public function getFields();

    /**
     * This method returns the fields for a given language and non translatable fields
     *
     * If note set the initialLanguage of the content version is used.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[] An array of {@link Field} with field identifier as keys
     */
    abstract public function getFieldsByLanguage( $languageCode = null );
}
