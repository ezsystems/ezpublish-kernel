<?php

/**
 * File containing the PolicyUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\User;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
 */
class PolicyUpdateStruct extends APIPolicyUpdateStruct
{
    /** @var \eZ\Publish\API\Repository\Values\User\Limitation[] */
    private $limitations;

    /**
     * Returns list of limitations added to policy.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    public function getLimitations()
    {
        return $this->limitations;
    }

    /**
     * Adds a limitation to the policy - if a Limitation exists with the same identifier
     * the existing limitation is replaced.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    public function addLimitation(Limitation $limitation)
    {
        $this->limitations[] = $limitation;
    }
}
