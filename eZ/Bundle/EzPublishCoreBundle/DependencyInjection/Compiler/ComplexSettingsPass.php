<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParserInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ComplexSettingsPass implements CompilerPassInterface
{
    /** @var ComplexSettingParserInterface */
    private $parser;

    public function __construct( ComplexSettingParserInterface $parser )
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
     * The factory has a variable number of arguments.
     * Dynamic settings are added as tupples: first the argument without the leading and trailing $, so that it is not
     * transformed by the config resolver pass, then the argument as a string, so that it does get transformed.
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

        $definition->setFactory(
            [
                'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingValueFactory',
                'getArgumentValue'
            ]
        );
        foreach ( $dynamicSettings as $dynamicSetting )
        {
            // Trim the '$'  so that the dynamic setting doesn't get transformed
            $definition->addArgument( trim( $dynamicSetting, '$' ) );

            // This one will be transformed
            $definition->addArgument( $dynamicSetting );
        }

        return $definition;
    }
}
