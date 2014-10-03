<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\AbstractParserTestCase;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension;
use Symfony\Component\DependencyInjection\Definition;

class EzPublishIOExtensionTest extends AbstractParserTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            // required to get the config resolver
            new EzPublishCoreExtension(),
            new EzPublishIOExtension()
        );
    }

    public function testDefaultHandler()
    {
        $this->load();
        $this->assertConfigResolverParameterValue( 'handler', 'filesystem', 'default' );
    }

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver
     */
    protected $configResolver;

    protected function load( array $configurationValues = array() )
    {
        parent::load( $configurationValues );
        $this->configResolver = $this->container->get( 'ezpublish.config.resolver.core' );
    }

    /**
     * Overrides the abstract parent's implementation to use the right namespace
     *
     * {@inheritdoc}
     */
    protected function assertConfigResolverParameterValue( $parameterName, $expectedValue, $scope, $assertSame = true )
    {
        $assertMethod = $assertSame ? 'assertSame' : 'assertEquals';
        $this->$assertMethod( $expectedValue, $this->configResolver->getParameter( $parameterName, 'ez_io', $scope ) );
    }
}
