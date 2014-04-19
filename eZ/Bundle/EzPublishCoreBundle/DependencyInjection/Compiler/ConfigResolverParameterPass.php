<?php
/**
 * File containing the ConfigResolverParameterPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will resolve config resolver parameters, delimited by $ chars (e.g. $my_parameter$).
 * It will replace those parameters by fake services having the config resolver as factory.
 * The factory method will then return the right value, at runtime.
 *
 * Supported syntax for parameters: $<paramName>[;<namespace>[;<scope>]]$
 *
 * @see \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface
 */
class ConfigResolverParameterPass implements CompilerPassInterface
{
    /**
     * @var DynamicSettingParserInterface
     */
    private $dynamicSettingParser;

    public function __construct( DynamicSettingParserInterface $dynamicSettingParser )
    {
        $this->dynamicSettingParser = $dynamicSettingParser;
    }

    public function process( ContainerBuilder $container )
    {
        $dynamicSettingsServices = array();
        // #1 Loop against all arguments of all service definitions to replace dynamic settings by the fake service.
        foreach ( $container->getDefinitions() as $id => $definition )
        {
            $replaceArguments = array();
            foreach ( $definition->getArguments() as $i => $arg )
            {
                if ( !$this->dynamicSettingParser->isDynamicSetting( $arg ) )
                {
                    continue;
                }

                $parsedParams = $this->dynamicSettingParser->parseDynamicSetting( $arg );
                $configResolverArgs = array(
                    $parsedParams['param'],
                    $parsedParams['namespace'],
                    $parsedParams['scope']
                );
                $paramConverter = new Definition( 'stdClass', $configResolverArgs );
                $paramConverter
                    ->setFactoryService( 'ezpublish.config.resolver' )
                    ->setFactoryMethod( 'getParameter' );

                $serviceId = 'ezpublish.config_resolver.fake.' . implode( '_', $configResolverArgs );
                if ( !$container->hasDefinition( $serviceId ) )
                {
                    $container->setDefinition( $serviceId, $paramConverter );
                }
                $replaceArguments[$i] = new Reference( $serviceId );
            }

            if ( empty( $replaceArguments ) )
            {
                continue;
            }

            $dynamicSettingsServices[$id] = true;
            foreach ( $replaceArguments as $i => $arg )
            {
                $definition->replaceArgument( $i, $arg );
            }
        }

        // #2 Loop again, to get all services depending on dynamic settings services.
        $dependentServices = array();
        foreach ( $container->getDefinitions() as $id => $definition )
        {
            $isDependent = false;
            foreach ( $definition->getArguments() as $arg )
            {
                if (
                    !(
                        $arg instanceof Reference
                        && isset( $dynamicSettingsServices[(string)$arg] )
                    )
                )
                {
                    continue;
                }

                $isDependent = true;
                break;
            }

            if ( $isDependent )
            {
                $dependentServices[] = $id;
            }
        }

        $resettableServices = array_unique(
            array_merge(
                array_keys( $dynamicSettingsServices ),
                $dependentServices
            )
        );
        $container->setParameter( 'ezpublish.config_resolver.resettable_services', $resettableServices );
    }
}
