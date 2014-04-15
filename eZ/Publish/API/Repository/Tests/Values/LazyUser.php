<?php
/**
 * File containing the eZ\Publish\API\Repository\Tests\Values\LazyUser class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\User\User as APIUser;

/**
 * This class represents a user value
 */
class LazyUser extends APIUser
{
    /**
     * Repository instance to be able to do lazy loading of user data
     *
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * User Id
     *
     * @var int|string
     */
    protected $userId;

    /**
     * Internal lazy loaded user
     *
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    protected $user;

    /**
     * @param Repository $repository
     * @param int|string $userId
     */
    public function __construct( Repository $repository, $userId )
    {
        $this->repository = $repository;
        $this->userId = $userId;
    }

    /**
     * Lazy load the content connected to this user
     *
     * return \eZ\Publish\API\Repository\Values\User\User
     */
    private function getInternalUser()
    {
        if ( $this->user === null )
        {
            $this->user = $this->repository->getUserService()->loadUser( $this->userId );
        }

        return $this->user;
    }

    /**
     * Returns the VersionInfo for this version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function getVersionInfo()
    {
        return $this->getInternalUser()->getVersionInfo();
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
        return $this->getInternalUser()->getFieldValue( $fieldDefIdentifier, $languageCode );
    }

    /**
     * This method returns the complete fields collection
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public function getFields()
    {
        return $this->getInternalUser()->getFields();
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
        return $this->getInternalUser()->getFieldsByLanguage( $languageCode );
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
                return $this->userId;
        }

        return $this->getInternalUser()->__get( $property );
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

        return $this->getInternalUser()->__isset( $property );
    }
}
