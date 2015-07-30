<?php

/**
 * File containing the ChainRoutingPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ChainRoutingPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.chain_router', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ChainRoutingPass());
    }

    /**
     * @param int|null $declaredPriority
     * @param int $expectedPriority
     *
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass::process
     * @dataProvider addRouterProvider
     */
    public function testAddRouter($declaredPriority, $expectedPriority)
    {
        $resolverDef = new Definition();
        $serviceId = 'some_service_id';
        if ($declaredPriority !== null) {
            $resolverDef->addTag('router', array('priority' => $declaredPriority));
        } else {
            $resolverDef->addTag('router');
        }

        $this->setDefinition($serviceId, $resolverDef);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.chain_router',
            'add',
            array(new Reference($serviceId), $expectedPriority)
        );
    }

    /**
     * @param int|null $declaredPriority
     * @param int $expectedPriority
     *
     * @covers eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass::process
     * @dataProvider addRouterProvider
     */
    public function testAddRouterWithDefaultRouter($declaredPriority, $expectedPriority)
    {
        $defaultRouter = new Definition();
        $this->setDefinition('router.default', $defaultRouter);

        $resolverDef = new Definition();
        $serviceId = 'some_service_id';
        if ($declaredPriority !== null) {
            $resolverDef->addTag('router', array('priority' => $declaredPriority));
        } else {
            $resolverDef->addTag('router');
        }

        $this->setDefinition($serviceId, $resolverDef);
        $this->compile();

        // Assertion for default router
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'router.default',
            'setSiteAccess',
            array(new Reference('ezpublish.siteaccess'))
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'router.default',
            'setConfigResolver',
            array(new Reference('ezpublish.config.resolver'))
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'router.default',
            'setNonSiteAccessAwareRoutes',
            array('%ezpublish.default_router.non_siteaccess_aware_routes%')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'router.default',
            'setSiteAccessRouter',
            array(new Reference('ezpublish.siteaccess_router'))
        );
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.chain_router',
            'add',
            array(new Reference('router.default'), 255)
        );

        // Assertion for all routers
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.chain_router',
            'add',
            array(new Reference($serviceId), $expectedPriority)
        );
    }

    public function addRouterProvider()
    {
        return array(
            array(null, 0),
            array(0, 0),
            array(57, 57),
            array(-23, -23),
            array(-255, -255),
            array(-256, -255),
            array(-1000, -255),
            array(255, 255),
            array(256, 255),
            array(1000, 255),
        );
    }
}
