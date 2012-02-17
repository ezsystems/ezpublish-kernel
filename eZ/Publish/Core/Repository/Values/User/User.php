<?php
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\User as APIUser,
    eZ\Publish\API\Repository\Values\Content\Content,

    eZ\Publish\Core\Base\Exceptions\PropertyNotFound,
    eZ\Publish\Core\Base\Exceptions\PropertyPermission;

/**
 * This class represents a user value
 *
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
     * Instance of Content value object that this user encapsulates
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected $content;

    /**
     * returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->content->getVersionInfo();
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
        return $this->content->getFieldValue( $fieldDefIdentifier, $languageCode );
    }

    /**
     * returns the outgoing relations
     *
     * @return array an array of {@link Relation}
     */
    public function getRelations()
    {
        return $this->content->getRelations();
    }

    /**
     * This method returns the complete fields collection
     *
     * @return array an array of {@link Field}
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
     * @return array an array of {@link Field} with field identifier as keys
     */
    public function getFieldsByLanguage( $languageCode = null )
    {
        return $this->content->getFieldsByLanguage( $languageCode );
    }

    /**
     * Magic get function handling read to non public properties
     * while also encapsulating content value object
     *
     * Returns value for all readonly (protected) properties.
     * Throws PropertyNotFound exception on all reads to undefined properties so typos are not silently accepted.
     *
     * @param string $property Name of the property
     *
     * @return mixed
     *
     * @throws PropertyNotFound When property does not exist
     */
    public function __get( $property )
    {
        if ( property_exists( $this->content, $property ) )
            return $this->content->$property;
        else if ( property_exists( $this, $property ) )
            return $this->$property;

        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Magic set function handling writes to non public properties
     * while also encapsulating content value object
     *
     * Throws PropertyNotFound exception on all writes to undefined properties so typos are not silently accepted and
     * throws PropertyPermission exception on readonly (protected) properties.
     *
     * @param string $property Name of the property
     * @param string $value
     *
     * @throws PropertyNotFound When property does not exist
     * @throws PropertyPermission When property is readonly (protected)
     */
    public function __set( $property, $value )
    {
        if ( property_exists( $this->content, $property ) )
            $this->content->$property = $value;
        else if ( property_exists( $this, $property ) )
            throw new PropertyPermission( $property, PropertyPermission::READ, get_class( $this ) );

        throw new PropertyNotFound( $property, get_class( $this ) );
    }

    /**
     * Magic isset function handling isset() to non public properties
     *
     * Returns true for all (public/)protected/private properties.
     *
     * @param string $property Name of the property
     *
     * @return boolean
     */
    public function __isset( $property )
    {
        return property_exists( $this->content, $property ) || property_exists( $this, $property );
    }

    /**
     * Magic unset function handling unset() to non public properties
     *
     * Throws PropertyNotFound exception on all writes to undefined properties so typos are not silently accepted and
     * throws PropertyPermission exception on readonly (protected) properties.
     *
     * @param string $property Name of the property
     *
     * @return boolean
     */
    public function __unset( $property )
    {
        $this->__set( $property, null );
    }
}
