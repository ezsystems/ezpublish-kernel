<?php
/**
 * File containing the PolicyUpdateStruct class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
 */
class PolicyUpdateStruct extends \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
{
    /**
     * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    private $limitations;

    /**
     * Returns list of limitations added to policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }

    /**
     * Adds a limitation to the policy - if a Limitation exists with the same identifier
     * the existing limitation is replaced
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     *
     * @return void
     */
    public function addLimitation( Limitation $limitation )
    {
        $this->limitations[] = $limitation;
    }

}
