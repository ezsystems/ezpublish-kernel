<?php
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
 *
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
     * @var \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    protected $fields;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    protected $relations;

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
     */
    public function getFieldValue( $fieldDefIdentifier, $languageCode = null )
    {
        $contentType  = $this->getContentType();
        $translatable = $contentType->getFieldDefinition( $fieldDefIdentifier )->isTranslatable;

        if ( null === $languageCode )
        {
            $languageCode = $this->getContentInfo()->mainLanguageCode;
        }

        foreach ( $this->getFields() as $field )
        {
            if ( $field->fieldDefIdentifier !== $fieldDefIdentifier )
            {
                continue;
            }

            if ( $translatable && $field->languageCode !== $languageCode )
            {
                continue;
            }

            return $field->value;
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
        return $this->fields;
    }

    /**
     * This method returns the fields for a given language and non translatable fields
     *
     * If note set the initialLanguage of the content version is used.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field} with field identifier as keys
     */
    public function getFieldsByLanguage( $languageCode = null )
    {
        $fields = array();
        $contentType  = $this->getContentType();

        if ( null === $languageCode )
        {
            $languageCode = $this->getContentInfo()->mainLanguageCode;
        }

        foreach ( $this->getFields() as $field )
        {
            if ( $contentType->getFieldDefinition( $field->fieldDefIdentifier )->isTranslatable
              && $field->languageCode !== $languageCode )
            {
                continue;
            }

            $fields[$field->fieldDefIdentifier] = $field;
        }

        return $fields;
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
}
