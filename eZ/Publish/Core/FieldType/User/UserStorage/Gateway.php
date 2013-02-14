<?php
/**
 * File containing the UserStorage Gateway
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\User\UserStorage;

use eZ\Publish\Core\FieldType\StorageGateway;

abstract class Gateway extends StorageGateway
{
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
     * - is_enabled
     * - is_locked
     * - last_visit
     * - login_count
     * - max_login
     *
     * @param mixed $fieldId
     * @param mixed $userId
     *
     * @return array
     */
    abstract public function getFieldData( $fieldId, $userId = null );
}

