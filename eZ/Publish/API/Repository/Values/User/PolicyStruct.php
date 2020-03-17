<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

abstract class PolicyStruct extends ValueObject
{
    /**
     * Returns list of limitations added to policy.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations(): iterable;

    /**
     * Adds a limitation with the given identifier and list of values.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    abstract public function addLimitation(Limitation $limitation): void;
}
