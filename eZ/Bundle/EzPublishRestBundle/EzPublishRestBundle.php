<?php

namespace eZ\Bundle\EzPublishRestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

class EzPublishRestBundle extends Bundle
{
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );
        $container->addCompilerPass( new Compiler\FieldTypeProcessorPass() );
        $container->addCompilerPass( new Compiler\InputHandlerPass() );
        $container->addCompilerPass( new Compiler\InputParserPass() );
        $container->addCompilerPass( new Compiler\OutputVisitorPass() );
        $container->addCompilerPass( new Compiler\ValueObjectVisitorPass() );
    }
}
