<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Policy class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a policy value
 *
 * @property-read mixed $id internal id of the policy
 * @property-read mixed $roleId the role id this policy belongs to
 * @property-read string $module Name of module, associated with the Policy
 * @property-read string $function  Name of the module function Or all functions with '*'
 * @property-read array $limitations an array of \eZ\Publish\API\Repository\Values\User\Limitation
 */
abstract class Policy extends ValueObject
{
    /**
     * ID of the policy
     *
     * @var mixed
     */
    protected $id;

    /**
     * the ID of the role this policy belongs to
     *
     * @var mixed
     */
    protected $roleId;

    /**
     * Name of module, associated with the Policy
     *
     * Eg: content
     *
     * @var string
     */
    protected $module;

    /**
     * Name of the module function Or all functions with '*'
     *
     * Eg: read
     *
     * @var string
     */
    protected $function;

    /**
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations();
}
