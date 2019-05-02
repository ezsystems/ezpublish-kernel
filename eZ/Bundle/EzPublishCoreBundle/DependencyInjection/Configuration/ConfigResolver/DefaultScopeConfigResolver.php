<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

final class DefaultScopeConfigResolver  extends ContainerBasedConfigResolver
{
    private const SCOPE_DEFAULT = 'default';

    protected function resolveScope(string $scope = null): string
    {
        return self::SCOPE_DEFAULT;
    }
}
