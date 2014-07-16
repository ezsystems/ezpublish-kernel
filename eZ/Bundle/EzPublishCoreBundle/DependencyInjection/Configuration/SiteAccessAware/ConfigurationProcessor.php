<?php
/**
 * File containing the ScopeConfigurationProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use Symfony\Component\DependencyInjection\ContainerInterface;
use InvalidArgumentException;

/**
 * Processor for SiteAccess aware configuration processing.
 * Use it when you want to map SiteAccess dependent semantic configuration to internal settings, readable
 * with the ConfigResolver.
 */
class ConfigurationProcessor
{
    /**
     * Registered configuration scopes.
     *
     * @var array
     */
    static protected $scopes;

    /**
     * Registered scope groups names, indexed by scope.
     *
     * @var array
     */
    static protected $groupsByScope;

    /**
     * Name of the node under which scope based (semantic) configuration takes place.
     *
     * @var string
     */
    protected $scopeNodeName;

    /**
     * @var ContextualizerInterface
     */
    protected $contextualizer;

    public function __construct( ContainerInterface $containerBuilder, $namespace, $scopeNodeName = 'system' )
    {
        $this->contextualizer = $this->buildContextualizer( $containerBuilder, $namespace, $scopeNodeName );
    }

    /**
     * Injects available configuration scopes.
     *
     * @param array $scopes
     */
    static public function setScopes( array $scopes )
    {
        static::$scopes = $scopes;
    }

    /**
     * Injects available scope groups, indexed by scope.
     *
     * @param array $groupsByScope
     */
    static public function setGroupsByScope( array $groupsByScope )
    {
        static::$groupsByScope = $groupsByScope;
    }

    /**
     * Triggers mapping process between semantic and internal configuration.
     *
     * @param array $config Parsed semantic configuration
     * @param ConfigurationMapper|callable $mapper Mapper to use. Can be either an instance of ConfigurationMapper or a callable.
     *                                             HookableConfigurationMapper can also be used. In this case, preMap()
     *                                             and postMap() will be also called respectively before and after the mapping loop.
     *
     *                                             If $mapper is a callable, it will the same arguments as defined in
     *                                             the signature defined in ConfigurationMapper interface:
     *                                             `array $scopeSettings, $currentScope, ContextualizerInterface $contextualizer`
     *
     * @throws \InvalidArgumentException
     */
    public function mapConfig( array $config, $mapper )
    {
        $mapperCallable = is_callable( $mapper );
        if ( !$mapperCallable && !$mapper instanceof ConfigurationMapper )
        {
            throw new InvalidArgumentException( 'Configuration mapper must either be a callable or an instance of ConfigurationMapper.' );
        }

        if ( $mapper instanceof HookableConfigurationMapper )
        {
            $mapper->preMap( $config, $this->contextualizer );
        }

        $scopeNodeName = $this->contextualizer->getScopeNodeName();
        foreach ( $config[$scopeNodeName] as $currentScope => &$scopeSettings )
        {
            if ( $mapperCallable )
            {
                call_user_func_array( $mapper, array( $scopeSettings, $currentScope, $this->contextualizer ) );
            }
            else
            {
                $mapper->mapConfig( $scopeSettings, $currentScope, $this->contextualizer );
            }
        }

        if ( $mapper instanceof HookableConfigurationMapper )
        {
            $mapper->postMap( $config, $this->contextualizer );
        }
    }

    /**
     * Builds configuration contextualizer (I know, sounds obvious...).
     * Override this method if you want to use your own contextualizer class.
     *
     * static::$scopes and static::$groupsByScope must be injected first.
     *
     * @param ContainerInterface $containerBuilder
     * @param string $namespace
     * @param string $scopeNodeName
     *
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface
     */
    protected function buildContextualizer( ContainerInterface $containerBuilder, $namespace, $scopeNodeName )
    {
        return new Contextualizer( $containerBuilder, $namespace, $scopeNodeName, static::$scopes, static::$groupsByScope );
    }

    /**
     * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface $contextualizer
     */
    public function setContextualizer( $contextualizer )
    {
        $this->contextualizer = $contextualizer;
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface
     */
    public function getContextualizer()
    {
        return $this->contextualizer;
    }
}
