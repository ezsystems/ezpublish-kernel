<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Limitation;

/**
 * Marker interface for PermissionResolver::canUser $targets objects.
 *
 * It's aimed to provide Limitations with information about intent (result of an action) to evaluate.
 *
 * @see \eZ\Publish\API\Repository\PermissionResolver::canUser
 */
interface Target
{
}
