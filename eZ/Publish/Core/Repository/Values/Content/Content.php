<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\Content class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\Repository\Values\ContentType\ContentType;

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
 */
class Content extends APIContent
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var integer
     */
    protected $contentId;

    /**
     * @var integer
     */
    protected $contentTypeId;

    /**
     * @var integer
     */
    protected $versionNo;

    /**
     * @var array an array of field values like $fields[$fieldDefIdentifier][$languageCode]
     */
    protected $fields;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    protected $relations;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    private $internalFields;

    function __construct( array $data = array() )
    {
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
        foreach ( $this->internalFields as $field )
        {
            $this->fields[$field->fieldDefIdentifier][$field->languageCode] = $field->value;
        }
    }

    /**
     * returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->repository->getContentService()->loadVersionInfoById(
            $this->contentId,
            $this->versionNo
        );
    }

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
     * @todo should an exception be thrown here if nothing is found?
     */
    public function getFieldValue( $fieldDefIdentifier, $languageCode = null )
    {
        if ( null === $languageCode )
        {
            $languageCode = $this->getContentInfo()->mainLanguageCode;
        }

        if ( isset( $this->fields[$fieldDefIdentifier][$languageCode] ) )
        {
            return $this->fields[$fieldDefIdentifier][$languageCode];
        }

        return null;
    }

    /**
     * returns the outgoing relations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[] An array of {@link Relation}
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * This method returns the complete fields collection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    public function getFields()
    {
        return $this->internalFields;
    }

    /**
     * This method returns the fields for a given language and non translatable fields
     *
     * If not set the initialLanguage of the content version is used.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field} with field identifier as keys
     */
    public function getFieldsByLanguage( $languageCode = null )
    {
        $fields = array();

        if ( null === $languageCode )
        {
            $languageCode = $this->getContentInfo()->mainLanguageCode;
        }

        foreach ( $this->getFields() as $field )
        {
            if ( $field->languageCode !== $languageCode ) continue;
            $fields[$field->fieldDefIdentifier] = $field;
        }

        return $fields;
    }

    /**
     * This method returns the field for a given field definition identifier and language
     *
     * If not set the initialLanguage of the content version is used.
     *
     * @param $fieldDefIdentifier
     * @param null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null A {@link Field} or null if nothing is found
     */
    public function getField( $fieldDefIdentifier, $languageCode = null )
    {
        if ( null === $languageCode )
        {
            $languageCode = $this->getContentInfo()->mainLanguageCode;
        }

        foreach ( $this->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier === $fieldDefIdentifier
                && $field->languageCode === $languageCode )
            {
                return $field;
            }
        }

        return null;
    }

    /**
     * Returns the underlying ContentType for this content object.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    private function getContentType()
    {
        return $this->repository->getContentTypeService()->loadContentType( $this->contentTypeId );
    }

    /**
     * Returns the content info for this concrete content.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private function getContentInfo()
    {
        return $this->repository->getContentService()->loadContentInfo( $this->contentId );
    }

    /**
     * Magic getter for retrieving convenience properties
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get( $property )
    {
        switch ( $property )
        {
            case 'contentType':
                return $this->getContentType();

            case 'contentInfo':
                return $this->getContentInfo();

            case 'versionInfo':
                return $this->getVersionInfo();
        }

        return parent::__get( $property );
    }

    /**
     * Magic isset for singaling existence of convenience properties
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset( $property )
    {
        if ( $property === 'contentType' )
            return true;

        if ( $property === 'contentInfo' )
            return true;

        if ( $property === 'versionInfo' )
            return true;

        return parent::__isset( $property );
    }
}
