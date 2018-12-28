<?php

/**
 * TrashService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\Core\Repository\Decorator\TrashServiceDecorator;

/**
 * TrashService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class TrashService extends TrashServiceDecorator
{
}
