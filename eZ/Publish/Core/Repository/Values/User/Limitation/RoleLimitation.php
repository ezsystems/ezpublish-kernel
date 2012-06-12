<?php
/**
 * File containing the eZ\Publish\Core\Repository\Values\User\Limitation\RoleLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation as APIRoleLimitation;

class RoleLimitation extends APIRoleLimitation
{
    /**
     *
     * the custom limitation name
     * @var string
     */
    private $name;

    /**
     * constructs a role limitation for the given limitation name
     * @param string $name
     */
    public function __construct( $name )
    {
        $this->name = $name;
    }

    /**
     * Returns the limitation identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->name;
    }
}
