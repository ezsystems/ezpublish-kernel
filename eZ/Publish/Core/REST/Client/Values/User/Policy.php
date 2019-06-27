<?php

/**
 * File containing the Policy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\Policy}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\Policy
 */
class Policy extends APIPolicy
{
    /** @var \eZ\Publish\API\Repository\Values\User\Limitation[] */
    protected $limitations = array();

    /**
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }
}
