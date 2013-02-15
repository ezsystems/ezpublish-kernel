<?php
/**
 * File containing the ConfigResolverInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC;

/**
 * Interface for config resolvers.
 *
 * Classes implementing this interface will help you get settings for a specific scope.
 * In eZ Publish context, this is useful to get a setting for a specific siteaccess for example.
 *
 * The idea is to check the different scopes available for a given namespace to find the appropriate parameter.
 * To work, the dynamic setting must comply internally to the following name format : "<namespace>.<scope>.parameter.name".
 */
interface ConfigResolverInterface
{
    /**
     * Returns value for $paramName, in $namespace.
     *
     * @param string $paramName The parameter name, without $prefix and the current scope (i.e. siteaccess name).
     * @param string $namespace Namespace for the parameter name. If null, the default namespace should be used.
     * @param string $scope The scope you need $paramName value for.
     *
     * @return mixed
     */
    public function getParameter( $paramName, $namespace = null, $scope = null );

    /**
     * Checks if $paramName exists in $namespace
     *
     * @param string $paramName
     * @param string $namespace If null, the default namespace should be used.
     * @param string $scope The scope you need $paramName value for.
     *
     * @return boolean
     */
    public function hasParameter( $paramName, $namespace = null, $scope = null );

    /**
     * Changes the default namespace to look parameter into.
     *
     * @param string $defaultNamespace
     */
    public function setDefaultNamespace( $defaultNamespace );

    /**
     * Returns the current default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace();
}
