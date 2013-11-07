<?php
/**
 * File containing the Role class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\MultiLanguageValueBase;
use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class Role extends MultiLanguageValueBase
{
    /**
     * ID of the user rule
     *
     * @var mixed
     */
    public $id;

    /**
     * Policies associated with the role
     *
     * @var \eZ\Publish\SPI\Persistence\User\Policy[]
     */
    public $policies = array();
}
