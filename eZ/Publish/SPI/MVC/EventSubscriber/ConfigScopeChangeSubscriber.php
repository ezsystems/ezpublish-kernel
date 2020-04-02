<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\EventSubscriber;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Lets implementing class react to config scope changes.
 */
interface ConfigScopeChangeSubscriber
{
    public function onConfigScopeChange(SiteAccess $siteAccess): void;
}
