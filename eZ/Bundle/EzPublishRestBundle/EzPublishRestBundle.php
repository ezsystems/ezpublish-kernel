<?php

namespace eZ\Bundle\EzPublishRestBundle;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Security\RestSessionBasedFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Configuration\Parser as ConfigParser;

class EzPublishRestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new Compiler\FieldTypeProcessorPass());
        $container->addCompilerPass(new Compiler\InputHandlerPass());
        $container->addCompilerPass(new Compiler\InputParserPass());
        $container->addCompilerPass(new Compiler\OutputVisitorPass());
        $container->addCompilerPass(new Compiler\ValueObjectVisitorPass());

        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension $eZExtension */
        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addConfigParser(new ConfigParser\RestResources());
        $eZExtension->addDefaultSettings(__DIR__ . '/Resources/config', ['default_settings.yml']);

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $securityExtension */
        $securityExtension = $container->getExtension('security');
        $securityExtension->addSecurityListenerFactory(new RestSessionBasedFactory());
    }
}
