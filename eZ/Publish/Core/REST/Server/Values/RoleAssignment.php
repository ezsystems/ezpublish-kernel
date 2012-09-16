<?php
/**
 * File containing the PolicyList class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;

/**
 * Role assignment view model
 */
class RoleAssignment
{
    /**
     * Role ID
     *
     * @var mixed
     */
    public $roleId;

    /**
     * Role limitation
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    public $limitation;

    /**
     * Construct
     *
     * @param mixed $roleId
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $limitation
     */
    public function __construct( $roleId, RoleLimitation $limitation = null )
    {
        $this->roleId = $roleId;
        $this->limitation = $limitation;
    }
}
