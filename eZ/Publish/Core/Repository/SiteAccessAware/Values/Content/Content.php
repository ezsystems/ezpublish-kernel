<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;

/**
 * @property-read mixed $id convenience getter for retrieving the contentId: $versionInfo->contentInfo->id
 * @property-read array $fields access fields, calls getFields()
 * @property-read string $name Name of the current version in a specific language (@see VersionInfo::$languageCode)
 * @property-read string $languageCode The language code of the version (@see VersionInfo::$name)
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo convenience getter for getVersionInfo()->getContentInfo()
 * @property-read \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo calls getVersionInfo()
 */
class Content extends APIContent
{
    const DYNAMIC_PROPERTIES = ['id', 'contentInfo', 'name', 'languageCode'];

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field[] An array of field values with $fieldDefIdentifier as a key
     */
    protected $fields = [];

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $versionInfo;

    /**
     * Returns the VersionInfo for this version.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->versionInfo;
    }

    /**
     * Returns a field value for the given identifier.
     * $version->fields[$fieldDefId] is an equivalent call
     *
     * This method no longer returns the value for a given language (object no longer contains fields in all languages).
     * It returns fields in a language of VersionInfo. $languageCode parameter is ignored.
     *
     * @param string $fieldDefIdentifier
     * @param string $languageCode (deprecated)
     *
     * @return mixed a primitive type or a field type Value object depending on the field type.
     */
    public function getFieldValue($fieldDefIdentifier, $languageCode = null)
    {
        return $this->getField($fieldDefIdentifier);
    }

    /**
     * This method returns the complete fields collection.
     *
     * It returns fields in a language of VersionInfo.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @deprecated
     *
     * This method no longer returns the fields for a given language (object no longer contains fields in all languages).
     * It returns fields in a language of VersionInfo. $languageCode parameter is ignored.
     *
     * @param string $languageCode (deprecated)
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field} with field identifier as keys
     */
    public function getFieldsByLanguage($languageCode = null)
    {
        return $this->getFields();
    }

    /**
     * This method returns the field for a given field definition identifier.
     *
     * This method no longer returns the field for a given language (object no longer contains fields in all languages).
     * It returns field in a language of VersionInfo. $languageCode parameter is ignored.
     *
     * @param string $fieldDefIdentifier
     * @param string|null $languageCode (deprecated)
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null A {@link Field} or null if nothing is found
     */
    public function getField($fieldDefIdentifier, $languageCode = null)
    {
        if (isset($this->fields[$fieldDefIdentifier])) {
            return $this->fields[$fieldDefIdentifier];
        }

        return null;
    }

    /**
     * Function where list of properties are returned.
     *
     * Override to add dynamic properties
     *
     * @uses parent::getProperties()
     *
     * @param array $dynamicProperties
     *
     * @return array
     */
    protected function getProperties($dynamicProperties = self::DYNAMIC_PROPERTIES)
    {
        return parent::getProperties($dynamicProperties);
    }

    /**
     * Magic getter for retrieving convenience properties.
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (in_array($property, self::DYNAMIC_PROPERTIES)) {
            switch ($property) {
                case 'id':
                    return $this->versionInfo->contentInfo->id;

                case 'contentInfo':
                    return $this->versionInfo->contentInfo;

                case 'name':
                    return $this->versionInfo->name;

                case 'languageCode':
                    return $this->versionInfo->languageCode;
            }
        }

        return parent::__get($property);
    }

    /**
     * Magic isset for signaling existence of convenience properties.
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        if (in_array($property, self::DYNAMIC_PROPERTIES)) {
            return true;
        }

        return parent::__isset($property);
    }
}