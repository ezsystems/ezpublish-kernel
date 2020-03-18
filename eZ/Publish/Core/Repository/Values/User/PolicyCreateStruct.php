<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class is used to create a policy.
 *
 * @internal Meant for internal use by Repository, type hint against API instead.
 */
class PolicyCreateStruct extends APIPolicyCreateStruct
{
    /**
     * List of limitations added to policy.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    protected $limitations = [];

    /**
     * Returns list of limitations added to policy.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations(): iterable
    {
        return $this->limitations;
    }

    /**
     * Adds a limitation with the given identifier and list of values.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    public function addLimitation(Limitation $limitation): void
    {
        $limitationIdentifier = $limitation->getIdentifier();
        $this->limitations[$limitationIdentifier] = $limitation;
    }
}
