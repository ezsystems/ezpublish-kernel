<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values;

/**
 * todo
 *
 * @property-read string $module
 * @property-read \eZ\Publish\API\Repository\Values\ValueObject $object
 * @property-read \eZ\Publish\API\Repository\Values\User\UserReference $userReference
 */
abstract class PermissionInfo extends ValueObject
{
    /**
     * @var string
     */
    protected $module;

    /**
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    protected $object;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\UserReference
     */
    protected $userReference;

    /**
     * todo
     *
     * @param string $function
     *
     * @return boolean
     */
    abstract public function canUser($function);

    /**
     * @return mixed
     */
    abstract public function getHash();
}
