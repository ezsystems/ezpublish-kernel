<?php
/**
 * File containing the ContentStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Content}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Content
 */
class ContentStub extends Content
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
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[]
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
        return $this->repository->getContentService()->loadVersionInfo( $this->getContentInfo() );
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
        // TODO: Implement getFieldValue() method.
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
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[] An array of {@link Field}
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
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field[] An array of {@link Field} with field identifier as keys
     */
    public function getFieldsByLanguage( $languageCode = null )
    {
        // TODO: Implement getFieldsByLanguage() method.
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