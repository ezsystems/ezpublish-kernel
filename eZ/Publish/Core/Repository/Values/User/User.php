<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\User class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
     * Internal content representation
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected $content;

    /**
     * Returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->content->getVersionInfo();
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
        return $this->content->getFieldValue( $fieldDefIdentifier, $languageCode );
    }

    /**
     * This method returns the complete fields collection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public function getFields()
    {
        return $this->content->getFields();
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
        return $this->content->getFieldsByLanguage( $languageCode );
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
            case 'contentInfo':
                return $this->getVersionInfo()->getContentInfo();

            case 'id':
                $versionInfo = $this->getVersionInfo();
                if ( empty( $versionInfo ) )
                {
                    return null;
                }
                return $versionInfo->getContentInfo()->id;

            case 'versionInfo':
                return $this->getVersionInfo();

            case 'fields':
                return $this->getFields();
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
        if ( $property === 'contentInfo' )
            return true;

        if ( $property === 'id' )
            return true;

        if ( $property === 'versionInfo' )
            return true;

        if ( $property === 'fields' )
            return true;

        return parent::__isset( $property );
    }
}
