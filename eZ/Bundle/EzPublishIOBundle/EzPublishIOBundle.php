<?php

namespace eZ\Bundle\EzPublishIOBundle;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\Configuration\Parser as IOConfigParser;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzPublishIOBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new Compiler\IOHandlerPass() );
    }

    public function getContainerExtension()
    {
        if ( !isset( $this->extension ) )
        {
            $this->extension = new EzPublishIOExtension(
                array(
                    new IOConfigParser\IO()
                )
            );
        }

        return $this->extension;
    }
}
