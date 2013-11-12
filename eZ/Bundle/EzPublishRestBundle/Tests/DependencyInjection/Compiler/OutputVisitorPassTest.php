<?php
/**
 * File containing the OutputVisitorPassTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\OutputVisitorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit_Framework_TestCase;

class OutputVisitorPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $regexp1 = array( '(^.*/.*$)' );
        $regexp2 = array( '(^application/json$)' );

        $stringDefinition = new Definition();
        $stringDefinition->addTag( 'ezpublish_rest.output.visitor', array( 'regexps' => 'ezpublish_rest.output.visitor.test1.regexps' ) );

        $arrayDefinition = new Definition();
        $arrayDefinition->addTag( 'ezpublish_rest.output.visitor', array( 'regexps' => array( $regexp1 ) ) );

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            array(
                'ezpublish_rest.output.visitor.dispatcher' => new Definition(),
                'ezpublish_rest.output.visitor.test_string' => $stringDefinition,
                'ezpublish_rest.output.visitor.test_array' => $arrayDefinition
            )
        );

        $containerBuilder->setParameter( 'ezpublish_rest.output.visitor.test1.regexps', array( $regexp2 ) );

        $compilerPass = new OutputVisitorPass();
        $compilerPass->process( $containerBuilder );

        $dispatcherMethodCalls = $containerBuilder->getDefinition( 'ezpublish_rest.output.visitor.dispatcher' )->getMethodCalls();
        self::assertTrue( isset( $dispatcherMethodCalls[0][0] ) );
        self::assertTrue( isset( $dispatcherMethodCalls[0][1] ) );
        self::assertEquals( 'addVisitor', $dispatcherMethodCalls[0][0] );
        self::assertEquals( 'addVisitor', $dispatcherMethodCalls[1][0] );
        self::assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $dispatcherMethodCalls[0][1][1] );
        self::assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $dispatcherMethodCalls[1][1][1] );

        self::assertEquals( 'ezpublish_rest.output.visitor.test_string', $dispatcherMethodCalls[0][1][1]->__toString() );
        self::assertEquals( 'ezpublish_rest.output.visitor.test_array', $dispatcherMethodCalls[1][1][1]->__toString() );
    }
}
