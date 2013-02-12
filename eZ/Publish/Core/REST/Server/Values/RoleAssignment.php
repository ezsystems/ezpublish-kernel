<?php
/**
 * File containing the RoleAssignment class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * RoleAssignment view model
 */
class RoleAssignment extends RestValue
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
