<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

interface SiteAccessConfiguration
{
    public function hasParameter(string $parameter): bool;

    public function getParameter(string $parameter);
}
