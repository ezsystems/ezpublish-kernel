<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class
 */
abstract class PolicyUpdateStruct extends ValueObject
{
    /**
     * Returns list of limitations added to policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations();

    /**
     * Adds a limitation to the policy - if a Limitation exists with the same identifier
     * the existing limitation is replaced
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    abstract public function addLimitation( Limitation $limitation );

}
