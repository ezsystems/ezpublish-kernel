<?php
/**
 * File containing the ContextualizerInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ContextualizerInterface
{
    /**
     * With this option, registerInternalConfigArray() will call array_unique() at the end of the merge process.
     * This will only work with normal arrays (i.e. not hashes) containing scalar values.
     */
    const UNIQUE = 1;

    /**
     * With this option, registerInternalConfigArray() will merge the hashes from the second level.
     * For instance:
     * array( 'full' => array( 1, 2, 3 ) ) and array( 'full' => array( 4, 5 ) )
     * will result in array( 'full' => array( 1, 2, 3, 4, 5 ) )
     */
    const MERGE_FROM_SECOND_LEVEL = 2;

    /**
     * Registers given parameter in container for given scope, in current namespace.
     * Resulting parameter will have format <namespace>.<scope>.<parameterName> .
     *
     * @param string $parameterName
     * @param string $scope
     * @param mixed $value
     */
    public function setContextualParameter( $parameterName, $scope, $value );

    /**
     * Registers and merges the internal scope configuration for array settings.
     * We merge arrays defined in scopes "default", in scope groups, in the scope itself and in the "global" scope.
     * To calculate the precedence of scope groups, they are alphabetically sorted.
     *
     * One may call this method from inside config parser's preScopeConfig() or postScopeConfig() method.
     *
     * @param string $id id of the setting array to register
     * @param array $config the full configuration as an array
     * @param int $options bit mask of options (@see constants of this class)
     */
    public function mapConfigArray( $id, array $config, $options = 0 );

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer();

    public function setContainer( ContainerInterface $container );

    /**
     * Injects namespace for internal settings.
     * Registered internal settings always have the format <namespace>.<scope>.<parameter_name>
     * e.g. ezsettings.default.session
     *
     * @param string $namespace
     */
    public function setNamespace( $namespace );

    /**
     * @return string
     */
    public function getNamespace();

    /**
     * Injects the name of the node under which scope based (semantic) configuration takes place.
     *
     * @param string $scopeNodeName
     */
    public function setScopeNodeName( $scopeNodeName );

    /**
     * @return string
     */
    public function getScopeNodeName();

    /**
     * Injects registered configuration scopes (e.g. SiteAccesses).
     *
     * @param array $availableScopes
     */
    public function setAvailableScopes( array $availableScopes );

    /**
     * @return array
     */
    public function getAvailableScopes();

    /**
     * Injects registered scope groups names, indexed by scope.
     * i.e. Which groups a SiteAccess is part of.
     *
     * @param array $groupsByScope
     */
    public function setGroupsByScope( array $groupsByScope );

    /**
     * @return array
     */
    public function getGroupsByScope();
}
