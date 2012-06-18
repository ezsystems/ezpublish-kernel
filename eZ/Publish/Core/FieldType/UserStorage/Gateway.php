<?php
/**
 * File containing the UserStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\UserStorage;

abstract class Gateway
{
    /**
     * Set dbHandler for gateway
     *
     * @param mixed $dbHandler
     * @return void
     */
    abstract public function setConnection( $dbHandler );

    /**
     * Get field data
     *
     * The User storage handles the following attributes, following the user field
     * type in eZ Publish 4:
     * - account_key
     * - has_stored_login
     * - contentobject_id
     * - login
     * - email
     * - password_hash
     * - password_hash_type
     * - is_logged_in
     * - is_enabled
     * - is_locked
     * - last_visit
     * - login_count
     * - max_login
     *
     * @param mixed $fieldId
     * @param mixed $userId
     * @return array
     */
    abstract public function getFieldData( $fieldId, $userId = null );

    /**
     * Store external field data
     *
     * @param mixed $fieldId
     * @param array $data
     * @return void
     */
    abstract public function storeFieldData( $fieldId, array $data );

    /**
     * Copy all field data
     *
     * @param mixed $fieldId
     * @return void
     */
    abstract public function copyFieldData( $fieldId );

    /**
     * Delete all field data
     *
     * @param mixed $fieldId
     * @return void
     */
    abstract public function deleteFieldData( $fieldId );
}

