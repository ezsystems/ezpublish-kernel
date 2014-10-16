<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\UserGroup class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\Core\Repository\Values\Content\ContentTrait;
use eZ\Publish\API\Repository\Values\User\UserGroup as APIAbstractUserGroup;

/**
 * This class represents a user group
 */
class UserGroup extends APIAbstractUserGroup
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
}
