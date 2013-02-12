<?php
/**
 * File containing the Content class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\Content;

use eZ\Publish\Core\REST\Client\ContentService;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Content}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Content
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo convenience getter for $versionInfo->contentInfo
 * @property-read \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType convenience getter for $versionInfo->contentInfo->contentType
 * @property-read mixed $id convenience getter for retrieving the contentId: $versionInfo->content->id
 * @property-read \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo calls getVersionInfo()
 * @property-read \eZ\Publish\API\Repository\Values\Content\Field[] $fields access fields, calls getFields()
 *
 * @todo Implement convenience property access!
 */
class Content extends \eZ\Publish\API\Repository\Values\Content\Content
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[][] Array of array of field values like $fields[$fieldDefIdentifier][$languageCode]
     */
    protected $fields;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $versionInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private $internalFields;

    /**
     * @var \eZ\Publish\Core\REST\Client\ContentService
     */
    protected $contentService;

    /**
     * Creates a new struct from the given $data array
     *
     * @param ContentService $contentService
     * @param array $data
     *
     * @access protected
     */
    function __construct( ContentService $contentService, array $data = array() )
    {
        $this->contentService = $contentService;
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
        foreach ( $this->internalFields as $field )
        {
            $this->fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        }
    }

    /**
     * Returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->versionInfo;
    }

    /**
     * Returns a field value for the given value
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
        if ( null === $languageCode )
        {
            $languageCode = $this->versionInfo->contentInfo->mainLanguageCode;
        }

        if ( isset( $this->fields[$fieldDefIdentifier][$languageCode] ) )
        {
            return $this->fields[$fieldDefIdentifier][$languageCode]->value;
        }

        return null;
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
            $languageCode = $this->versionInfo->contentInfo->mainLanguageCode;
        }

        foreach ( $this->getFields() as $field )
        {
            if ( $field->languageCode === $languageCode )
            {
                $fields[$field->fieldDefIdentifier] = $field;
            }
        }

        return $fields;
    }

    /**
     * This method returns the field for a given field definition identifier and language
     *
     * If not set the initialLanguage of the content version is used.
     *
     * @param string $fieldDefIdentifier
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null A {@link Field} or null if nothing is found
     */
    public function getField( $fieldDefIdentifier, $languageCode = null )
    {
        if ( null === $languageCode )
        {
            $languageCode = $this->versionInfo->contentInfo->mainLanguageCode;
        }

        if ( isset( $this->fields[$fieldDefIdentifier][$languageCode] ) )
        {
            return $this->fields[$fieldDefIdentifier][$languageCode];
        }

        return null;
    }
}
