<?php
/**
 * File containing the IOConfigurationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ComplexSettingsPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ComplexSettingsPassTest extends AbstractCompilerPassTestCase
{
    /** @var ComplexSettingParser|\PHPUnit_Framework_MockObject_MockObject */
    private $parserMock;

    public function setUp()
    {
        parent::setUp();
    }

    protected function registerCompilerPass( ContainerBuilder $container )
    {
        $container->addCompilerPass( new ComplexSettingsPass( new ComplexSettingParser() ) );
    }

    public function testProcess()
    {
        $this->setDefinition(
            'service1',
            new Definition(
                'stdClass', array( '/mnt/nfs/$var_dir$/$storage_dir$' )
            )
        );

        $this->setDefinition(
            'service_with_array',
            new Definition(
                'stdClass', array( array( 'foo' ) )
            )
        );

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'service1.__complex_setting_factory_0',
            'setDynamicSetting',
            array( array( '$var_dir$' ), '$var_dir$' )
        );

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'service1',
            0,
            new Reference( 'service1.__complex_setting_factory_0' )
        );
    }
}
