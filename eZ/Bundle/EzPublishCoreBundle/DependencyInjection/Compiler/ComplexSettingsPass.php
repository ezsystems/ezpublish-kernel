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
     * Tests if $string contains dynamic settings with extra strings.
     * @param string $string
     * @return bool
     */
    private function containsDynamicSettings( $string )
    {
        $dollarsCount = substr_count( $string, '$' );
        if ( $dollarsCount < 2 )
        {
            return false;
        }

        // The string IS a dynamic variable, not our job
        if ( $this->isDynamicSetting( $string ) )
        {
            return false;
        }

        // Now let's see if it really contains dynamic variables

    }

    private function isDynamicSetting( $string )
    {
        return ( preg_match( "^\$[a-z0-9_\.]+(?:;[a-z0-9_\.]+){0,2}\$$", $string ) );
    }

    /**
     * @param $argumentValue
     * @param $dynamicSettings
     *
     * @return Definition
     */
    private function createFactoryDefinition( $argumentValue, $dynamicSettings )
    {
        $definition = new Definition(
            'stdClass',
            array( $argumentValue )
        );

        $definition->setFactoryClass( 'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingValueFactory' );
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
