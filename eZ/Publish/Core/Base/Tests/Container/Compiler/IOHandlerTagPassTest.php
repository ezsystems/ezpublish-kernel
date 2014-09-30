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
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition( 'ezpublish.core.io.factory', new Definition() );
    }

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new IOHandlerTagPass() );
    }

    public function testLocaleListener()
    {
        $ioHandlerIdentifier = 'io_handler_identifier';
        $ioHandlerServiceId = 'io_handler';
        $def = new Definition();
        $def->addTag( 'ezpublish.io_handler', array( 'alias' => $ioHandlerIdentifier ) );
        $this->setDefinition( $ioHandlerServiceId, $def );

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.core.io.factory',
            'setHandlersMap',
            array( array( $ioHandlerIdentifier => $ioHandlerServiceId ) )
        );
    }
}
