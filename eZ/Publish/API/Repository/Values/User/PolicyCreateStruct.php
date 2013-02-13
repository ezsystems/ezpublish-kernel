<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\PolicyCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to create a policy
 */
abstract class PolicyCreateStruct extends ValueObject
{
    /**
     * Name of module, associated with the Policy
     *
     * Eg: content
     *
     * @var string
     */
    public $module;

    /**
     * Name of the module function Or all functions with '*'
     *
     * Eg: read
     *
     * @var string
     */
    public $function;

    /**
     * Returns list of limitations added to policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations();

    /**
     * Adds a limitation with the given identifier and list of values
     * @param Limitation $limitation
     */
    abstract public function addLimitation( Limitation $limitation );
}
