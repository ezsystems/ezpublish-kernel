<?php
/**
 * File containing the CreatedRole class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a freshly created role.
 */
class CreatedRole extends ValueObject
{
    /**
     * The created role
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    public $role;
}
