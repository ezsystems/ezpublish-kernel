<?php

/**
 * File containing the ConfigResolverParameterPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ConfigResolverParameterPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use PHPUnit_Framework_TestCase;

class ConfigResolverParameterPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->setParameter('ezpublish.config_resolver.updateable_services', array());
        $updateableServices = array();
        $def1Arg1 = 'foo';
        $def1Arg2 = new Reference('foo.bar');
        $def1 = new Definition('stdClass', array($def1Arg1, $def1Arg2));
        $def2 = new Definition('stdClass', array('$bar;some_namespace$', array()));
        $def3 = new Definition('stdClass', array('$content.default_ttl;ezsettings;ezdemo_site_admin$'));
        $def4 = new Definition('stdClass', array('$languages$'));
        $def5Arg1 = new Reference('def3');
        $def5 = new Definition('stdClass', array($def5Arg1));
        $def6Arg1 = new Reference('def1');
        $def6Arg2 = new Reference('def4');
        $def6 = new Definition('stdClass', array($def6Arg1, $def6Arg2));
        $def7MethodCalls = array(
            array('setFoo', array('something', new Reference('def1'))),
            array('setBar', array(array('baz'))),
        );
        $def7 = new Definition('stdClass');
        $def7->setMethodCalls($def7MethodCalls);
        $def8MethodCalls = array(
            array('setFoo', array('$foo$')),
            array('setBar', array('$bar;baz$')),
        );
        $def8 = new Definition('stdClass');
        $def8->setMethodCalls($def8MethodCalls);
        $container->setDefinitions(
            array(
                'def1' => $def1,
                'def2' => $def2,
                'def3' => $def3,
                'def4' => $def4,
                'def5' => $def5,
                'def6' => $def6,
                'def7' => $def7,
                'def8' => $def8,
            )
        );

        $configResolverPass = new ConfigResolverParameterPass(new DynamicSettingParser());
        $configResolverPass->process($container);

        // Ensure that non concerned services stayed untouched.
        self::assertSame($def1Arg1, $def1->getArgument(0));
        self::assertSame($def1Arg2, $def1->getArgument(1));
        self::assertSame($def1, $container->getDefinition('def1'));
        self::assertSame($def5Arg1, $def5->getArgument(0));
        self::assertSame($def5, $container->getDefinition('def5'));
        self::assertSame($def6Arg1, $def6->getArgument(0));
        self::assertSame($def6Arg2, $def6->getArgument(1));
        self::assertSame($def6, $container->getDefinition('def6'));
        self::assertSame($def7MethodCalls, $def7->getMethodCalls());
        self::assertSame($def7, $container->getDefinition('def7'));

        // Check that concerned services arguments have been correctly transformed.
        $def2arg1 = $def2->getArgument(0);
        self::assertInstanceOf('Symfony\Component\ExpressionLanguage\Expression', $def2arg1);
        self::assertSame('service("ezpublish.config.resolver").getParameter("bar", "some_namespace", null)', (string)$def2arg1);
        // Also check 2nd argument
        self::assertSame(array(), $def2->getArgument(1));

        $def3arg1 = $def3->getArgument(0);
        self::assertInstanceOf('Symfony\Component\ExpressionLanguage\Expression', $def3arg1);
        self::assertSame('service("ezpublish.config.resolver").getParameter("content.default_ttl", "ezsettings", "ezdemo_site_admin")', (string)$def3arg1);

        $def4arg1 = $def4->getArgument(0);
        self::assertInstanceOf('Symfony\Component\ExpressionLanguage\Expression', $def4arg1);
        self::assertSame('service("ezpublish.config.resolver").getParameter("languages", null, null)', (string)$def4arg1);

        $def8Calls = $def8->getMethodCalls();
        self::assertSame(count($def8MethodCalls), count($def8Calls));
        self::assertSame($def8MethodCalls[0][0], $def8Calls[0][0]);
        self::assertInstanceOf('Symfony\Component\ExpressionLanguage\Expression', $def8Calls[0][1][0]);
        $exprSetFoo = 'service("ezpublish.config.resolver").getParameter("foo", null, null)';
        self::assertSame($exprSetFoo, (string)$def8Calls[0][1][0]);
        self::assertSame($def8MethodCalls[1][0], $def8Calls[1][0]);
        self::assertInstanceOf('Symfony\Component\ExpressionLanguage\Expression', $def8Calls[1][1][0]);
        $exprSetBar = 'service("ezpublish.config.resolver").getParameter("bar", "baz", null)';
        self::assertSame($exprSetBar, (string)$def8Calls[1][1][0]);
        $updateableServices['def8'] = array(
            array('setFoo', $exprSetFoo),
            array('setBar', $exprSetBar),
        );

        self::assertTrue($container->hasParameter('ezpublish.config_resolver.resettable_services'));
        self::assertEquals(
            array('def2', 'def3', 'def4', 'def5', 'def6'),
            $container->getParameter('ezpublish.config_resolver.resettable_services')
        );
        self::assertEquals(
            $updateableServices,
            $container->getParameter('ezpublish.config_resolver.updateable_services')
        );
    }
}
