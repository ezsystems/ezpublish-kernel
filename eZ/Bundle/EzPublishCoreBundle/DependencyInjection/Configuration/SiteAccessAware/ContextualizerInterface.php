<?php

/**
 * File containing the ContextualizerInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ContextualizerInterface
{
    /**
     * With this option, mapConfigArray() will call array_unique() at the end of the merge process.
     * This will only work with normal arrays (i.e. not hashes) containing scalar values.
     */
    const UNIQUE = 1;

    /**
     * With this option, mapConfigArray() will merge the hashes from the second level.
     * For instance:
     * array( 'full' => array( 1, 2, 3 ) ) and array( 'full' => array( 4, 5 ) )
     * will result in array( 'full' => array( 1, 2, 3, 4, 5 ) ).
     */
    const MERGE_FROM_SECOND_LEVEL = 2;

    /**
     * Defines a contextual parameter in the container for given scope in current namespace.
     * Resulting parameter will have format <namespace>.<scope>.<parameterName> .
     *
     * ```php
     * <?php
     * namespace Acme\DemoBundle\DependencyInjection;
     *
     * use Symfony\Component\HttpKernel\DependencyInjection\Extension;
     * use Symfony\Component\DependencyInjection\ContainerBuilder;
     * use Symfony\Component\DependencyInjection\Loader;
     * use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;
     *
     * class AcmeDemoExtension extends Extension
     * {
     *     public function load( array $configs, ContainerBuilder $container )
     *     {
     *         $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
     *
     *         $configuration = $this->getConfiguration( $configs, $container );
     *         $config = $this->processConfiguration( $configuration, $configs );
     *
     *         // ...
     *         $processor = new SiteAccessAware\ConfigurationProcessor( $container, 'acme_demo' );
     *         $processor->mapConfig(
     *             $config,
     *             function ( array $scopeSettings, $currentScope, SiteAccessAware\ContextualizerInterface $contextualizer )
     *             {
     *                 // Value of 'some_semantic_parameter' will be stored as a container parameter under
     *                 // key acme_demo.<$currentScope>.my_internal_parameter
     *                 $contextualizer->setContextualParameter( 'my_internal_parameter', $currentScope, $scopeSettings['some_semantic_parameter'] );
     *             }
     *         );
     *     }
     * }
     * ```
     *
     * @param string $parameterName
     * @param string $scope
     * @param mixed $value
     */
    public function setContextualParameter($parameterName, $scope, $value);

    /**
     * Maps a semantic setting to internal format for all declared scopes.
     * Resulting parameter will have format <namespace>.<scope>.<id> .
     *
     * @param string $id Id of the setting to map.
     *                   Note that it will be used to identify the semantic setting in $config and to define the internal
     *                   setting in the container (<namespace>.<scope>.<$id>)
     * @param array $config Full semantic configuration array for current bundle.
     *
     * @return mixed
     */
    public function mapSetting($id, array $config);

    /**
     * Maps semantic array settings to internal format, and merges them between scopes.
     *
     * This is useful when you have e.g. a hash of settings defined in a siteaccess group and you want an entry of
     * this hash, defined at the siteaccess or global level, to replace the one in the group.
     *
     * Defined arrays are merged in the following scopes:
     *
     * * `default`
     * * siteaccess groups
     * * siteaccess
     * * `global`
     *
     * To calculate the precedence of siteaccess groups, they are alphabetically sorted.
     *
     * Example:
     *
     * ```yaml
     * acme_demo:
     *     system:
     *         my_siteaccess_group:
     *             foo_setting:
     *                 foo: "bar"
     *                 some: "thing"
     *                 an_integer: 123
     *                 enabled: false
     *
     *         # Assuming my_siteaccess is part of my_siteaccess_group
     *         my_siteaccess:
     *             foo_setting:
     *                 an_integer: 456
     *                 enabled: true
     * ```
     *
     * In your DIC extension
     *
     * ```php
     * namespace Acme\DemoBundle\DependencyInjection;
     *
     * use Symfony\Component\HttpKernel\DependencyInjection\Extension;
     * use Symfony\Component\DependencyInjection\ContainerBuilder;
     * use Symfony\Component\DependencyInjection\Loader;
     * use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;
     *
     * class AcmeDemoExtension extends Extension
     * {
     *     public function load( array $configs, ContainerBuilder $container )
     *     {
     *         $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
     *
     *         $configuration = $this->getConfiguration( $configs, $container );
     *         $config = $this->processConfiguration( $configuration, $configs );
     *
     *         // ...
     *         $processor = new SiteAccessAware\ConfigurationProcessor( $container, 'acme_demo' );
     *         $contextualizer = $processor->getContextualizer();
     *         $contextualizer->mapConfigArray( 'foo_setting', $configs );
     *
     *         $processor->mapConfig(
     *             $config,
     *             function ( array $scopeSettings, $currentScope, SiteAccessAware\ContextualizerInterface $contextualizer )
     *             {
     *                 // ...
     *             }
     *         );
     *     }
     * }
     * ```
     *
     * This will result with having following parameters in the container:
     *
     * ```yaml
     * acme_demo.my_siteaccess.foo_setting:
     *     foo: "bar"
     *     some: "thing"
     *     an_integer: 456
     *     enabled: true
     *
     * acme_demo.my_siteaccess_gorup.foo_setting
     *     foo: "bar"
     *         some: "thing"
     *         an_integer: 123
     *         enabled: false
     * ```
     *
     * @param string $id Id of the setting array to map.
     *                   Note that it will be used to identify the semantic setting in $config and to define the internal
     *                   setting in the container (<namespace>.<scope>.<$id>)
     * @param array $config Full semantic configuration array for current bundle.
     * @param int $options Bit mask of options (see constants of the interface)
     */
    public function mapConfigArray($id, array $config, $options = 0);

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer();

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Injects namespace for internal settings.
     * Registered internal settings always have the format <namespace>.<scope>.<parameter_name>
     * e.g. ezsettings.default.session.
     *
     * @param string $namespace
     */
    public function setNamespace($namespace);

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * Injects the name of the node under which scope based (semantic) configuration takes place.
     *
     * @param string $scopeNodeName
     */
    public function setSiteAccessNodeName($scopeNodeName);

    /**
     * @return string
     */
    public function getSiteAccessNodeName();

    /**
     * Injects registered SiteAccesses (i.e. configuration scopes).
     *
     * @param array $availableSiteAccesses
     */
    public function setAvailableSiteAccesses(array $availableSiteAccesses);

    /**
     * @return array
     */
    public function getAvailableSiteAccesses();

    /**
     * Injects names of registered SiteAccess groups, indexed by SiteAccess.
     * i.e. Which groups a SiteAccess is part of.
     *
     * @param array $groupsBySiteAccess
     */
    public function setGroupsBySiteAccess(array $groupsBySiteAccess);

    /**
     * @return array
     */
    public function getGroupsBySiteAccess();
}
