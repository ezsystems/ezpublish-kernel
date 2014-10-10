<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DynamicSettingsPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        foreach ( $container->getDefinitions() as $definition )
        {
            foreach( $definition->getArguments() as $argumentIndex => $argumentValue )
            {
                if ( !$this->containsDynamicSettings( $argumentValue ) )
                {
                    return;
                }

                if ( !$this->isDynamicSetting( $argumentValue ) )
                {
                    return;
                }

                foreach( $this->parser->getDynamicSettings( $argumentValue ) as $dynamicSetting )
                {

                }
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
}
