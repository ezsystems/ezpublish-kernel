<?php
/**
 * File containing the IOHandlerTagPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Tests\Container\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\LocalePass;
use eZ\Publish\Core\Base\Container\Compiler\IOHandlerTagPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class IOHandlerTagPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new IOHandlerTagPass() );
    }

    public function testMetadataHandler()
    {
        $ioHandlerServiceId = 'some_handler';
        $ioHandlerAlias = 'some_alias_handler';
        $def = new Definition();
        $def->addTag( 'ezpublish.io.metadata_handler', array( 'alias' => $ioHandlerAlias ) );
        $this->setDefinition( $ioHandlerServiceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasParameter(
            'ez_io.available_metadata_handler_types',
            array( $ioHandlerAlias => $ioHandlerServiceId )
        );
    }

    public function testBinarydataHandler()
    {
        $ioHandlerServiceId = 'some_handler';
        $ioHandlerAlias = 'some_alias_handler';
        $def = new Definition();
        $def->addTag( 'ezpublish.io.binarydata_handler', array( 'alias' => $ioHandlerAlias ) );
        $this->setDefinition( $ioHandlerServiceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasParameter(
            'ez_io.available_binarydata_handler_types',
            array( $ioHandlerAlias => $ioHandlerServiceId )
        );
    }
}
