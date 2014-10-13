<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ComplexSettingsPass implements CompilerPassInterface
{
    /** @var ComplexSettingParser */
    private $parser;

    public function __construct( ComplexSettingParser $parser )
    {
        $this->parser = $parser;
    }

    public function process( ContainerBuilder $container )
    {
        foreach ( $container->getDefinitions() as $serviceId => $definition )
        {
            $arguments = $definition->getArguments();
            foreach ( $arguments as $argumentIndex => $argumentValue )
            {
                if ( !is_string( $argumentValue ) )
                {
                    continue;
                }

                if ( !$this->parser->containsDynamicSettings( $argumentValue ) )
                {
                    continue;
                }

                if ( $this->parser->isDynamicSetting( $argumentValue ) )
                {
                    continue;
                }

                $factoryServiceId = sprintf( '%s.%s_%d', $serviceId, '__complex_setting_factory', $argumentIndex );
                $container->setDefinition(
                    $factoryServiceId,
                    $this->createFactoryDefinition(
                        $argumentValue,
                        $this->parser->parseComplexSetting( $argumentValue )
                    )
                );

                $arguments[$argumentIndex] = new Reference( $factoryServiceId );
                $definition->setArguments( $arguments );
            }
        }
    }

    /**
     * Creates a complex setting factory.
     *
     * The factory has a variable number of argumentsdynamic settings are added as tupples:
     * first the argument in an array, so that it is not transformed by the config resolver pass, then the argument
     * as a string, so that it does get transformed.
     *
     * @param string $argumentValue The original argument ($var$/$another_var$)
     * @param array $dynamicSettings Array of dynamic settings in $argumentValue
     *
     * @return Definition
     */
    private function createFactoryDefinition( $argumentValue, $dynamicSettings )
    {
        $definition = new Definition(
            'stdClass',
            array( $argumentValue )
        );

        $definition->setFactoryClass(
            'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingValueFactory'
        );
        $definition->setFactoryMethod( 'getArgumentValue' );
        foreach ( $dynamicSettings as $dynamicSetting )
        {
            // the setting won't be transformed in an array
            $definition->addArgument( array( $dynamicSetting ) );

            // this one will be transformed
            $definition->addArgument( $dynamicSetting );
        }

        return $definition;
    }
}
