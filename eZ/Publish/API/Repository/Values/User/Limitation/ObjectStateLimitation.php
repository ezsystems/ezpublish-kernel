<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Class ObjectStateLimitation
 *
 * This Object state serves as API limitation for "StateGroup" from legacy,
 * StateGroup stored a combination of StateGroup identifier as well as State id(s) while this one
 * only cares about the state id's.
 *
 * @package eZ\Publish\API\Repository\Values\User\Limitation
 */
class ObjectStateLimitation extends Limitation
{
    /**
     * @see \eZ\Publish\API\Repository\Values\User\Limitation::getIdentifier()
     *
     * @return string
     */
    public function getIdentifier()
    {
        return Limitation::STATE;
    }
}
