<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\User class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\Core\Repository\Values\Content\ContentTrait;
use eZ\Publish\API\Repository\Values\User\User as APIAbstractUser;

/**
 * This class represents a user value
 *
 *  *
  * @see \eZ\Publish\API\Repository\Values\User\User
 */
class User extends APIAbstractUser
{
    use ContentTrait;

    /**
     * Constructs User object
     *
     * @param array $data Must contain the following properties:
     * - internalFields (for ContentTrait)
     * - versionInfo (for ContentTrait)
     * - login
     * - email
     * - passwordHash
     * - hashAlgorithm
     * - enabled
     * - maxLogin
     */
    public function __construct( array $data = array() )
    {
        $this->init( $data );
    }

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
}
