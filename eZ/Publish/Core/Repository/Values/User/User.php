<?php
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
     * Content info for the user
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * Content type for the user
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected $contentType;

    /**
     * Content id for the user
     *
     * @var int
     */
    protected $contentId;

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
        //@todo: implement
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
        //@todo: implement
    }
}
