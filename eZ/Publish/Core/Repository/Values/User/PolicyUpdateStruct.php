<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class.
 */
class PolicyUpdateStruct extends APIPolicyUpdateStruct
{
    /**
     * List of limitations added to policy.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    protected $limitations = array();

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
        $limitationIdentifier = $limitation->getIdentifier();
        $this->limitations[$limitationIdentifier] = $limitation;
    }
}
