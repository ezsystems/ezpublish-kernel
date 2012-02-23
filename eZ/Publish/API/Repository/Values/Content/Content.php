<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * this class represents a content object in a specific version
 */
abstract class Content extends ValueObject
{
    /**
     * returns the id of this content
     * convenience getter for getVersionInfo()->getContentInfo()->contentId
     *
     * @return int
     */
    abstract public function getContentId();

    /**
     * returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    abstract public function getVersionInfo();

    /**
     * returns the ContentInfo for this version
     * convenience getter for getVersionInfo()->getContentInfo()
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    abstract public function getContentInfo();

    /**
     * returns the ContentType for content to which this version belongs
     * convenience getter for getContentInfo()->getContentType()
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    abstract public function getContentType();

    /**
     * returns a field value for the given value
     * $version->fields[$fieldDefId][$languageCode] is an equivalent call
     * if no language is given on a translatable field this method returns
     * the value of the initial language of the version if present, otherwise null.
     * On non translatable fields this method ignores the languageCode parameter.
     *
     * @param string $fieldDefIdentifier
     * @param string|null $languageCode
     *
     * @return mixed a primitive type or a field type Value object depending on the field type.
     */
    abstract public function getFieldValue( $fieldDefIdentifier, $languageCode = null );

    /**
     * returns the outgoing relations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    abstract public function getRelations();

    /**
     * This method returns the complete fields collection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[]
     */
    abstract public function getFields();

    /**
     * This method returns the fields for a given language and non translatable fields
     *
     * If note set the initialLanguage of the content version is used.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[]
     */
    abstract public function getFieldsByLanguage( $languageCode = null );
}
