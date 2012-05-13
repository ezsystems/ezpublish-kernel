<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\User class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\User as APIUser;

/**
 * This class represents a user value
 */
class User extends APIUser
{
    /**
     * @var int MD5 of password, not recommended
     */
    const PASSWORD_HASH_MD5_PASSWORD = 1;

    /**
     * @var int MD5 of user and password
     */
    const PASSWORD_HASH_MD5_USER = 2;

    /**
     * @var int MD5 of site, user and password
     */
    const PASSWORD_HASH_MD5_SITE = 3;

    /**
     * @var int Passwords in plaintext, should not be used for real sites
     */
    const PASSWORD_HASH_PLAINTEXT = 5;

    /**
     * Version info for the user
     *
     * @var \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    protected $versionInfo;

    /**
     * Fields that this user has
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    protected $fields = array();

    /**
     * Relations from this user to other content
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    protected $relations = array();

    /**
     * returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->versionInfo;
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
        $contentInfo = $this->getVersionInfo()->getContentInfo();
        $contentType = $contentInfo->getContentType();
        $isTranslatable = $contentType->getFieldDefinition( $fieldDefIdentifier )->isTranslatable;

        $languageCodeToUse = $contentInfo->mainLanguageCode;
        if ( $isTranslatable && $languageCode !== null )
            $languageCodeToUse = $languageCode;

        foreach ( $this->fields as $field )
        {
            if ( $field->fieldDefIdentifier === $fieldDefIdentifier && $field->languageCode === $languageCodeToUse )
                return $field->value;
        }

        return null;
    }

    /**
     * returns the outgoing relations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * This method returns the complete fields collection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
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
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] with field identifier as keys
     */
    public function getFieldsByLanguage( $languageCode = null )
    {
        $contentInfo = $this->getVersionInfo()->getContentInfo();
        $contentType = $contentInfo->getContentType();

        $languageCodeToUse = $contentInfo->mainLanguageCode;
        if ( $languageCode !== null )
            $languageCodeToUse = $languageCode;

        $fieldsToReturn = array();

        foreach ( $this->fields as $field )
        {
            $isTranslatable = $contentType->getFieldDefinition( $field->fieldDefIdentifier )->isTranslatable;
            if ( !$isTranslatable || $field->languageCode === $languageCodeToUse )
                $fieldsToReturn[] = $field;
        }

        return $fieldsToReturn;
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
            case 'contentInfo':
                return $this->versionInfo->getContentInfo();

            case 'contentType':
                return $this->versionInfo->getContentInfo()->getContentType();

            case 'id':
                if ( empty( $this->versionInfo ) )
                    return null;
                return $this->versionInfo->getContentInfo()->id;
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

        if ( $property === 'id' )
            return true;

        return parent::__isset( $property );
    }
}
