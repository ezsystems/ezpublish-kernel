<?php

/**
 * File containing the AbstractParserTestCase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

abstract class AbstractParserTestCase extends AbstractExtensionTestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver */
    protected $configResolver;

    protected function load(array $configurationValues = [])
    {
        parent::load($configurationValues);
        $this->configResolver = $this->container->get('ezpublish.config.resolver.core');
    }

    /**
     * Asserts a parameter from ConfigResolver has expected value for given scope.
     *
     * @param string $parameterName
     * @param mixed $expectedValue
     * @param string $scope SiteAccess name, group, default or global
     * @param bool $assertSame Set to false if you want to use assertEquals() instead of assertSame()
     */
    protected function assertConfigResolverParameterValue($parameterName, $expectedValue, $scope, $assertSame = true)
    {
        $assertMethod = $assertSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod($expectedValue, $this->configResolver->getParameter($parameterName, 'ezsettings', $scope));
    }
}
