<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class.
 */
abstract class PolicyUpdateStruct extends PolicyStruct
{
}
