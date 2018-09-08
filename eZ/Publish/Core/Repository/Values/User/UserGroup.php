<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\UserGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\UserGroup as APIUserGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * This class represents a user group.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class UserGroup extends APIUserGroup
{
    /**
     * Internal content representation.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected $content;

    /**
     * Returns the VersionInfo for this version.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->content->getVersionInfo();
    }

    public function getContentType(): ContentType
    {
        return $this->content->getContentType();
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
    public function getFieldValue($fieldDefIdentifier, $languageCode = null)
    {
        return $this->content->getFieldValue($fieldDefIdentifier, $languageCode);
    }

    /**
     * This method returns the complete fields collection.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public function getFields()
    {
        return $this->content->getFields();
    }

    /**
     * This method returns the fields for a given language and non translatable fields.
     *
     * If note set the initialLanguage of the content version is used.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] with field identifier as keys
     */
    public function getFieldsByLanguage($languageCode = null)
    {
        return $this->content->getFieldsByLanguage($languageCode);
    }

    /**
     * This method returns the field for a given field definition identifier and language.
     *
     * If not set the initialLanguage of the content version is used.
     *
     * @param string $fieldDefIdentifier
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null A {@link Field} or null if nothing is found
     */
    public function getField($fieldDefIdentifier, $languageCode = null)
    {
        return $this->content->getField($fieldDefIdentifier, $languageCode);
    }

    /**
     * Function where list of properties are returned.
     *
     * Override to add dynamic properties
     *
     * @uses \parent::getProperties()
     *
     * @param array $dynamicProperties
     *
     * @return array
     */
    protected function getProperties($dynamicProperties = ['id', 'contentInfo', 'versionInfo', 'fields'])
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
        switch ($property) {
            case 'contentInfo':
                return $this->getVersionInfo()->getContentInfo();

            case 'id':
                return $this->getVersionInfo()->getContentInfo()->id;

            case 'versionInfo':
                return $this->getVersionInfo();

            case 'fields':
                return $this->getFields();

            case 'content':
                // trigger error for this, but for BC let it pass on to normal __get lookup for now
                @trigger_error(
                    sprintf('%s is and internal property, usage is deprecated as of 6.10. UserGroup itself exposes everything needed.', $property),
                    E_USER_DEPRECATED
                );
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
        if ($property === 'contentInfo') {
            return true;
        }

        if ($property === 'id') {
            return true;
        }

        if ($property === 'versionInfo') {
            return true;
        }

        if ($property === 'fields') {
            return true;
        }

        return parent::__isset($property);
    }
}
