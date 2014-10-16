<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\Content\ContentTrait trait.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\Content;

/**
 * Trait ContentTrait
 * @package eZ\Publish\Core\Repository\Values\Content
 */
trait ContentTrait
{
    /**
     * @var mixed[][] An array of array of field values like $fields[$fieldDefIdentifier][$languageCode]
     */
    protected $fields;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $versionInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    private $internalFields = array();

    /**
     * To be used by constructor
     *
     * @param array $data Must contain the following properties:
     * - internalFields
     * - versionInfo
     */
    private function init( array $data = array() )
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
            return $this->fields[$fieldDefIdentifier][$languageCode];
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
     * @param string $fieldDefIdentifier
     * @param null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null A {@link Field} or null if nothing is found
     */
    public function getField( $fieldDefIdentifier, $languageCode = null )
    {
        if ( null === $languageCode )
        {
            $languageCode = $this->versionInfo->contentInfo->mainLanguageCode;
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
     * Function where list of properties are returned
     *
     * Override to add dynamic properties
     * @uses parent::getProperties()
     *
     * @param array $dynamicProperties
     *
     * @return array
     */
    protected function getProperties( $dynamicProperties = array( 'id', 'contentInfo' ) )
    {
        return parent::getProperties( $dynamicProperties );
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
            case 'id':
                return $this->versionInfo->contentInfo->id;

            case 'contentInfo':
                return $this->versionInfo->contentInfo;
        }

        return parent::__get( $property );
    }

    /**
     * Magic isset for signaling existence of convenience properties
     *
     * @param string $property
     *
     * @return boolean
     */
    public function __isset( $property )
    {
        if ( $property === 'id' )
            return true;

        if ( $property === 'contentInfo' )
            return true;

        return parent::__isset( $property );
    }
}
