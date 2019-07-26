<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\BeforeDeletePolicyEvent as BeforeDeletePolicyEventInterface;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;

final class BeforeDeletePolicyEvent extends BeforeEvent implements BeforeDeletePolicyEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\Policy */
    private $policy;

    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    public function getPolicy(): Policy
    {
        return $this->policy;
    }
}
