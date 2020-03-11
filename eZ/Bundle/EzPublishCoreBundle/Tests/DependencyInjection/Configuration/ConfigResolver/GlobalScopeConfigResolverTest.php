<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\ConfigResolver;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\GlobalScopeConfigResolver;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

class GlobalScopeConfigResolverTest extends ConfigResolverTest
{
    protected function getResolver(string $defaultNamespace = self::DEFAULT_NAMESPACE): ConfigResolverInterface
    {
        $configResolver = new GlobalScopeConfigResolver(
            $defaultNamespace
        );
        $configResolver->setContainer($this->containerMock);

        return $configResolver;
    }

    protected function getScope(): string
    {
        return 'global';
    }
}
