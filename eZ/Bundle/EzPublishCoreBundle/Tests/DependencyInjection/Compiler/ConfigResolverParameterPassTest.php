<?php
/**
 * File containing the ConfigResolverParameterPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
        $def1Arg1 = 'foo';
        $def1Arg2 = new Reference( 'foo.bar' );
        $def1 = new Definition( 'stdClass', array( $def1Arg1, $def1Arg2 ) );
        $def2 = new Definition( 'stdClass', array( '$bar;some_namespace$', array() ) );
        $def3 = new Definition( 'stdClass', array( '$content.default_ttl;ezsettings;ezdemo_site_admin$' ) );
        $def4 = new Definition( 'stdClass', array( '$languages$' ) );
        $def5Arg1 = new Reference( 'def3' );
        $def5 = new Definition( 'stdClass', array( $def5Arg1 ) );
        $def6Arg1 = new Reference( 'def1' );
        $def6Arg2 = new Reference( 'def4' );
        $def6 = new Definition( 'stdClass', array( $def6Arg1, $def6Arg2 ) );
        $container->setDefinitions(
            array(
                'def1' => $def1,
                'def2' => $def2,
                'def3' => $def3,
                'def4' => $def4,
                'def5' => $def5,
                'def6' => $def6,
            )
        );

        $configResolverPass = new ConfigResolverParameterPass( new DynamicSettingParser() );
        $configResolverPass->process( $container );

        // Ensure that non concerned services stayed untouched.
        $this->assertSame( $def1Arg1, $def1->getArgument( 0 ) );
        $this->assertSame( $def1Arg2, $def1->getArgument( 1 ) );
        $this->assertSame( $def1, $container->getDefinition( 'def1' ) );
        $this->assertSame( $def5Arg1, $def5->getArgument( 0 ) );
        $this->assertSame( $def5, $container->getDefinition( 'def5' ) );
        $this->assertSame( $def6Arg1, $def6->getArgument( 0 ) );
        $this->assertSame( $def6Arg2, $def6->getArgument( 1 ) );
        $this->assertSame( $def6, $container->getDefinition( 'def6' ) );

        // Check that concerned services arguments have been correctly transformed.
        /** @var Reference $def2arg1 */
        $def2arg1 = $def2->getArgument( 0 );
        $this->assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $def2arg1 );
        $expectedServiceHelperId1 = 'ezpublish.config_resolver.fake.bar_some_namespace_';
        $this->assertSame( (string)$def2arg1, $expectedServiceHelperId1 );
        $this->assertTrue( $container->has( $expectedServiceHelperId1 ) );
        $defHelper1 = $container->getDefinition( $expectedServiceHelperId1 );
        $this->assertSame( 'ezpublish.config.resolver', $defHelper1->getFactoryService() );
        $this->assertSame( 'getParameter', $defHelper1->getFactoryMethod() );
        $this->assertSame(
            array( 'bar', 'some_namespace', null ),
            $defHelper1->getArguments()
        );
        // Also check 2nd argument
        $this->assertSame( array(), $def2->getArgument( 1 ) );

        /** @var Reference $def3arg1 */
        $def3arg1 = $def3->getArgument( 0 );
        $this->assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $def3arg1 );
        $expectedServiceHelperId2 = 'ezpublish.config_resolver.fake.content.default_ttl_ezsettings_ezdemo_site_admin';
        $this->assertSame( (string)$def3arg1, $expectedServiceHelperId2 );
        $this->assertTrue( $container->has( $expectedServiceHelperId2 ) );
        $defHelper2 = $container->getDefinition( $expectedServiceHelperId2 );
        $this->assertSame( 'ezpublish.config.resolver', $defHelper2->getFactoryService() );
        $this->assertSame( 'getParameter', $defHelper2->getFactoryMethod() );
        $this->assertSame(
            array( 'content.default_ttl', 'ezsettings', 'ezdemo_site_admin' ),
            $defHelper2->getArguments()
        );

        /** @var Reference $def4arg1 */
        $def4arg1 = $def4->getArgument( 0 );
        $this->assertInstanceOf( 'Symfony\\Component\\DependencyInjection\\Reference', $def4arg1 );
        $expectedServiceHelperId3 = 'ezpublish.config_resolver.fake.languages__';
        $this->assertSame( (string)$def4arg1, $expectedServiceHelperId3 );
        $this->assertTrue( $container->has( $expectedServiceHelperId3 ) );
        $defHelper3 = $container->getDefinition( $expectedServiceHelperId3 );
        $this->assertSame( 'ezpublish.config.resolver', $defHelper3->getFactoryService() );
        $this->assertSame( 'getParameter', $defHelper3->getFactoryMethod() );
        $this->assertSame(
            array( 'languages', null, null ),
            $defHelper3->getArguments()
        );

        $this->assertTrue( $container->hasParameter( 'ezpublish.config_resolver.resettable_services' ) );
        $this->assertEquals(
            array( 'def2', 'def3', 'def4', 'def5', 'def6' ),
            $container->getParameter( 'ezpublish.config_resolver.resettable_services' )
        );
    }
}
