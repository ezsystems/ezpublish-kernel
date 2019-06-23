<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\Policy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;

/**
 * This class represents a policy value.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class Policy extends APIPolicy
{
    /**
     * Limitations assigned to this policy.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    protected $limitations = [];

    /**
     * Returns the list of limitations for this policy.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }
}
