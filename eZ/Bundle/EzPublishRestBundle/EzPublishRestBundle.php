<?php

namespace eZ\Bundle\EzPublishRestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\FieldTypeProcessorPass;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\OutputVisitorPass;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\ValueObjectVisitorPass;

class EzPublishRestBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new FieldTypeProcessorPass() );
        $container->addCompilerPass( new OutputVisitorPass() );
        $container->addCompilerPass( new ValueObjectVisitorPass() );
    }
}
