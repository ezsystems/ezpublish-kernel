<?php

/**
 * File containing the ConfigResolverInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
    public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null);

    /**
     * Checks if $paramName exists in $namespace.
     *
     * @param string $paramName The parameter name, without $prefix and the current scope (i.e. siteaccess name).
     * @param string $namespace Namespace for the parameter name. If null, the default namespace should be used.
     * @param string $scope The scope you need $paramName value for.
     */
    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool;

    /**
     * Changes the default namespace to look parameter into.
     */
    public function setDefaultNamespace(string $defaultNamespace): void;

    /**
     * Returns the current default namespace.
     */
    public function getDefaultNamespace(): string;
}
