<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Role;

use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;

interface UpdatePolicyEvent
{
    public function getPolicy(): Policy;

    public function getPolicyUpdateStruct(): PolicyUpdateStruct;

    public function getUpdatedPolicy(): Policy;
}
