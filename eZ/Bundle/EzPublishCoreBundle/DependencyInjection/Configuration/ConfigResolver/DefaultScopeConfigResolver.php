<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

class DefaultScopeConfigResolver extends ContainerBasedConfigResolver
{
    private const SCOPE_NAME = 'default';

    public function __construct(string $defaultNamespace)
    {
        parent::__construct(self::SCOPE_NAME, $defaultNamespace);
    }
}
