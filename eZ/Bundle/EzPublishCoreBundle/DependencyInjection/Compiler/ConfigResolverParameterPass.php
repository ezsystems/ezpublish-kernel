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
use Symfony\Component\DependencyInjection\ContainerInterface;
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
        $resettableServices = array();
        $fakeServices = array();
        // Pass #1 Loop against all arguments of all service definitions to replace dynamic settings by the fake service.
        foreach ( $container->getDefinitions() as $serviceId => $definition )
        {
            // Constructor injection
            $replaceArguments = array();
            foreach ( $definition->getArguments() as $i => $arg )
            {
                if ( !$this->dynamicSettingParser->isDynamicSetting( $arg ) )
                {
                    continue;
                }

                // Decorators use index_X for their key
                if ( strpos( $i, 'index' ) === 0 )
                {
                    $i = (int)substr( $i, strlen( 'index_' ) );
                }
                $fakeServiceId = $this->injectFakeService( $container, $this->getConfigResolverArgs( $arg ) );
                $replaceArguments[$i] = new Reference( $fakeServiceId, ContainerInterface::NULL_ON_INVALID_REFERENCE );
                $fakeServices[] = $fakeServiceId;
            }

            if ( !empty( $replaceArguments ) )
            {
                $dynamicSettingsServices[$serviceId] = true;
                foreach ( $replaceArguments as $i => $arg )
                {
                    $definition->replaceArgument( $i, $arg );
                }
            }

            // Setter injection
            $methodCalls = $definition->getMethodCalls();
            foreach ( $methodCalls as $i => &$call )
            {
                list( $method, $callArgs ) = $call;
                foreach ( $callArgs as &$callArg )
                {
                    if ( !$this->dynamicSettingParser->isDynamicSetting( $callArg ) )
                    {
                        continue;
                    }

                    $fakeServiceId = $this->injectFakeService( $container, $this->getConfigResolverArgs( $callArg ) );
                    $callArg = new Reference( $fakeServiceId, ContainerInterface::NULL_ON_INVALID_REFERENCE );
                    $fakeServices[] = $fakeServiceId;
                }

                $call = array( $method, $callArgs );
            }

            $definition->setMethodCalls( $methodCalls );
        }

        // Pass #2 Loop again, to get all services depending on dynamic settings services.
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
                $resettableServices[] = $id;
            }
        }

        $resettableServices = array_unique(
            array_merge(
                array_keys( $dynamicSettingsServices ),
                $resettableServices
            )
        );
        $container->setParameter( 'ezpublish.config_resolver.resettable_services', $resettableServices );
        $container->setParameter( 'ezpublish.config_resolver.dynamic_settings_services', array_unique( $fakeServices ) );
    }

    /**
     * @param string $dynamicSetting
     *
     * @return array
     */
    private function getConfigResolverArgs( $dynamicSetting )
    {
        $parsedParams = $this->dynamicSettingParser->parseDynamicSetting( $dynamicSetting );
        $configResolverArgs = array(
            $parsedParams['param'],
            $parsedParams['namespace'],
            $parsedParams['scope']
        );

        return $configResolverArgs;
    }

    /**
     * @param ContainerBuilder $container
     * @param $configResolverArgs
     *
     * @return string
     */
    private function injectFakeService( ContainerBuilder $container, $configResolverArgs )
    {
        $paramConverter = new Definition( 'stdClass', $configResolverArgs );
        $paramConverter
            ->setFactory( [ new Reference( 'ezpublish.config.resolver' ), 'getParameter' ] )
            // @deprecated Synchronized services are deprecated in Symfony 2.7 in favour of RequestStack
            // see: https://github.com/symfony/symfony/pull/13289
            ->setSynchronized( true, false );

        $serviceId = 'ezpublish.config_resolver.fake.' . implode( '_', $configResolverArgs );
        if ( !$container->hasDefinition( $serviceId ) )
        {
            $container->setDefinition( $serviceId, $paramConverter );
        }

        return $serviceId;
    }
}
