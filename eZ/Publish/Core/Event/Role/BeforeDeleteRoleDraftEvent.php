<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Role;

use eZ\Publish\API\Repository\Events\Role\BeforeDeleteRoleDraftEvent as BeforeDeleteRoleDraftEventInterface;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;

final class BeforeDeleteRoleDraftEvent extends BeforeEvent implements BeforeDeleteRoleDraftEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\RoleDraft */
    private $roleDraft;

    public function __construct(RoleDraft $roleDraft)
    {
        $this->roleDraft = $roleDraft;
    }

    public function getRoleDraft(): RoleDraft
    {
        return $this->roleDraft;
    }
}
