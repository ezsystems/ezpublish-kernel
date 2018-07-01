<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\PolicyCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

/**
 * This class is used to create a policy.
 */
abstract class PolicyCreateStruct extends PolicyStruct
{
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
}
