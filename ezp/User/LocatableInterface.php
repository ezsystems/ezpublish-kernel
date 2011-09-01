<?php
/**
 * File contains LocatableInterface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;

/**
 * User Locatable Interface
 *
 * A interface for classes that is cable of having locations ( user & user group )
 */
interface LocatableInterface
{
    /**
     * @return \ezp\User\Location[]
     */
    public function getLocations();
}
