<?php
/**
 * File containing the InputHandlerPassTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\InputHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit_Framework_TestCase;

class InputHandlerPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $visitorDefinition = new Definition();
        $visitorDefinition->addTag( 'ezpublish_rest.input.handler', array( 'format' => 'test' ) );

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            array(
                'ezpublish_rest.input.dispatcher' => new Definition(),
                'ezpublish_rest.input.handler.test' => $visitorDefinition,
            )
        );

        $compilerPass = new InputHandlerPass;
        $compilerPass->process( $containerBuilder );

        $dispatcherMethodCalls = $containerBuilder
            ->getDefinition( 'ezpublish_rest.input.dispatcher' )
            ->getMethodCalls();
        self::assertTrue( isset( $dispatcherMethodCalls[0][0] ), "Failed asserting that dispatcher has a method call" );
        self::assertEquals( 'addHandler', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addParser'" );
        self::assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $dispatcherMethodCalls[0][1][1], "Failed asserting that method call is to a Reference object" );

        self::assertEquals( 'ezpublish_rest.input.handler.test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.input.handler.test'" );
    }
}
