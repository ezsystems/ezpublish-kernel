<?php

/**
 * File containing the SecurityPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SecurityPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SecurityPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('security.authentication.provider.dao', new Definition());
        $this->setDefinition('security.authentication.provider.rememberme', new Definition());
        $this->setDefinition('security.authentication.provider.anonymous', new Definition());
        $this->setDefinition('security.http_utils', new Definition());
        $this->setDefinition('security.authentication.success_handler', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SecurityPass());
    }

    public function testAlteredDaoAuthenticationProvider()
    {
        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.authentication.provider.dao',
            'setRepository',
            [new Reference('ezpublish.api.repository')]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.authentication.provider.rememberme',
            'setRepository',
            [new Reference('ezpublish.api.repository')]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.authentication.provider.anonymous',
            'setRepository',
            [new Reference('ezpublish.api.repository')]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.http_utils',
            'setSiteAccess',
            [new Reference('ezpublish.siteaccess')]
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'security.authentication.success_handler',
            'setConfigResolver',
            [new Reference('ezpublish.config.resolver')]
        );
    }
}
