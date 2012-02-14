<?php
/**
 * File containing the UserStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\User;

use \eZ\Publish\API\Repository\Values\User\User;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\User}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\User
 */
class UserStub extends User
{
    /**
     * returns the VersionInfo for this version
     *
     * @return VersionInfo
     */
    public function getVersionInfo()
    {
        // TODO: Implement getVersionInfo() method.
    }

    /**
     * returns a field value for the given value
     * $version->fields[$fieldDefId][$languageCode] is an equivalent call
     * if no language is given on a translatable field this method returns
     * the value of the initial language of the version if present, otherwise null.
     * On non translatable fields this method ignores the languageCode parameter.
     *
     * @param string $fieldDefIdentifer
     * @param string $languageCode
     *
     * @return mixed a primitive type or a field type Value object depending on the field type.
     */
    public function getFieldValue( $fieldDefIdentifer, $languageCode = null )
    {
        // TODO: Implement getFieldValue() method.
    }

    /**
     * returns the outgoing relations
     *
     * @return array an array of {@link Relation}
     */
    public function getRelations()
    {
        // TODO: Implement getRelations() method.
    }

    /**
     * This method returns the complete fields collection
     *
     * @return array an array of {@link Field}
     */
    public function getFields()
    {
        // TODO: Implement getFields() method.
    }

    /**
     * This method returns the fields for a given language and non translatable fields
     *
     * If note set the initilaLanguage of the content version is used.
     *
     * @param string $languageCode
     *
     * @return array an array of {@link Field} with field identifier as keys
     */
    public function getFieldsByLanguage( $languageCode = null )
    {
        // TODO: Implement getFieldsByLanguage() method.
    }

}
