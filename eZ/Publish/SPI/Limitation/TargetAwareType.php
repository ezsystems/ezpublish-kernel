<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject as APIValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;

/**
 * Represents Limitation type.
 * Indicates that Limitation Type implementation properly supports $targets passed as instances of Target.
 *
 * @see \eZ\Publish\SPI\Limitation\Type
 * @see \eZ\Publish\SPI\Limitation\Target
 */
interface TargetAwareType extends Type
{
    /**
     * Evaluate ("Vote") against a main value object and targets for the context.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $currentUser
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\SPI\Limitation\Target[]|null $targets $targets An array of location, parent or "assignment"
     *                                                                 objects, if null: none where provided by caller
     *
     * @return bool|null Returns one of ACCESS_* constants
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     */
    public function evaluate(
        APILimitationValue $value,
        APIUserReference $currentUser,
        APIValueObject $object,
        array $targets = null
    ): ?bool;
}
