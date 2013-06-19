<?php

namespace eZ\Bundle\EzPublishRestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\FieldTypeProcessorPass;

class EzPublishRestBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new FieldTypeProcessorPass() );
    }
}
