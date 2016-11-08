<?php

/**
 * File containing the Policy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 */
class Policy extends ValueObject
{
    /**
     * ID of the policy.
     *
     * @var mixed
     */
    public $id;

    /**
     * Foreign ID of the role.
     *
     * @var mixed
     */
    public $roleId;

    /**
     * Only used when the role's status, current policy belongs to, is Role::STATUS_DRAFT.
     * Original policy ID the draft was created from.
     * In other cases, will be null.
     *
     * @since 6.0
     *
     * @var int|null
     */
    public $originalId;

    /**
     * Name of module, associated with the Policy.
     *
     * Eg: content
     *
     * @var string
     */
    public $module;

    /**
     * Name of the module function Or all functions with '*'.
     *
     * Eg: read
     *
     * @var string
     */
    public $function;

    /**
     * Array of policy limitations, which is just a random hash map.
     *
     * The limitation array may look like:
     * <code>
     *  array(
     *      'Subtree' => array(
     *          '/1/2/',
     *          '/1/4/',
     *      ),
     *      'Foo' => array( 'Bar' ),
     *      …
     *  )
     * </code>
     *
     * Where the keys are the limitation identifiers, and the respective values
     * are an array of limitation values
     *
     * @var array|string If string, then only the value '*' is allowed, meaning all limitations.
     *                   Can not be a empty array as '*' should be used in this case.
     */
    public $limitations;

    /**
     * Return true if at least one of the given policies overlaps this policy (has a wider scope).
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy[] $policies
     * @return bool
     */
    public function overlaps(array $policies)
    {
        foreach ($policies as $policy) {
            // a policy can overlap other policy only if it has no limitations
            if ($policy->limitations === '*' &&
                ($this->module === $policy->module || $policy->module === '*') &&
                ($this->function === $policy->function || $policy->function === '*')
            ) {
                return true;
            }
        }

        return false;
    }
}
