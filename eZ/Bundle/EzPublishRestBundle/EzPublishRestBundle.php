<?php

namespace eZ\Bundle\EzPublishRestBundle;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Security\RestSessionBasedFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

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

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $securityExtension */
        $securityExtension = $container->getExtension('security');
        $securityExtension->addSecurityListenerFactory(new RestSessionBasedFactory());
    }
}
