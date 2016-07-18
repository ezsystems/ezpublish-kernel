<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\PermissionResolver;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference;

/**
 * todo
 */
abstract class PermissionInfoMapper
{
    /**
     * todo
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     *
     * @return bool
     */
    abstract public function canMap(ValueObject $object);

    /**
     * todo
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $userReference
     *
     * @return \eZ\Publish\API\Repository\Values\PermissionInfo
     */
    abstract public function map(ValueObject $object, UserReference $userReference);
}
