<?php

/**
 * File containing the ConfigResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * This class will help you get settings for a specific scope.
 * This is useful to get a setting for a specific siteaccess for example.
 *
 * It will check the different scopes available for a given namespace to find the appropriate parameter.
 * To work, the dynamic setting must comply internally to the following name format : "<namespace>.<scope>.parameter.name".
 *
 * - <namespace> is the namespace for your dynamic setting. Defaults to "ezsettings", but can be anything.
 * - <scope> is basically the siteaccess name you want your parameter value to apply to.
 *   Can also be "global" for a global override.
 *   Another scope is used internally: "default". This is the generic fallback.
 *
 * The resolve scope order is the following:
 * 1. "global"
 * 2. SiteAccess name
 * 3. "default"
 */
class ConfigResolver extends ContainerAware implements VersatileScopeInterface, SiteAccessAware
{
    const SCOPE_GLOBAL = 'global',
          SCOPE_DEFAULT = 'default';

    const UNDEFINED_STRATEGY_EXCEPTION = 1,
          UNDEFINED_STRATEGY_NULL = 2;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    protected $siteAccess;

    /**
     * @var array Siteaccess groups, indexed by siteaccess name
     */
    protected $groupsBySiteAccess;

    /**
     * @var string
     */
    protected $defaultNamespace;

    /**
     * @var string
     */
    protected $defaultScope;

    /**
     * @var int
     */
    protected $undefinedStrategy;

    /**
     * @param array $groupsBySiteAccess SiteAccess groups, indexed by siteaccess.
     * @param string $defaultNamespace The default namespace
     * @param int $undefinedStrategy Strategy to use when encountering undefined parameters.
     *                               Must be one of
     *                                  - ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION (throw an exception)
     *                                  - ConfigResolver::UNDEFINED_STRATEGY_NULL (return null)
     */
    public function __construct(
        array $groupsBySiteAccess,
        $defaultNamespace,
        $undefinedStrategy = self::UNDEFINED_STRATEGY_EXCEPTION
    ) {
        $this->groupsBySiteAccess = $groupsBySiteAccess;
        $this->defaultNamespace = $defaultNamespace;
        $this->undefinedStrategy = $undefinedStrategy;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
        $this->defaultScope = $siteAccess->name;
    }

    /**
     * Sets the strategy to use if an undefined parameter is being asked.
     * Can be one of:
     *  - ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION (throw an exception)
     *  - ConfigResolver::UNDEFINED_STRATEGY_NULL (return null).
     *
     * Defaults to ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION.
     *
     * @param int $undefinedStrategy
     */
    public function setUndefinedStrategy($undefinedStrategy)
    {
        $this->undefinedStrategy = $undefinedStrategy;
    }

    /**
     * @return int
     */
    public function getUndefinedStrategy()
    {
        return $this->undefinedStrategy;
    }

    /**
     * Checks if $paramName exists in $namespace.
     *
     * @param string $paramName
     * @param string $namespace If null, the default namespace should be used.
     * @param string $scope The scope you need $paramName value for. It's typically the siteaccess name.
     *                      If null, the current siteaccess name will be used.
     *
     * @return bool
     */
    public function hasParameter($paramName, $namespace = null, $scope = null)
    {
        $namespace = $namespace ?: $this->defaultNamespace;
        $scope = $scope ?: $this->defaultScope;

        $defaultScopeParamName = "$namespace." . self::SCOPE_DEFAULT . ".$paramName";
        $globalScopeParamName = "$namespace." . self::SCOPE_GLOBAL . ".$paramName";
        $relativeScopeParamName = "$namespace.$scope.$paramName";

        // Relative scope, siteaccess group wise
        $groupScopeHasParam = false;
        if (isset($this->groupsBySiteAccess[$scope])) {
            foreach ($this->groupsBySiteAccess[$scope] as $groupName) {
                $groupScopeParamName = "$namespace.$groupName.$paramName";
                if ($this->container->hasParameter($groupScopeParamName)) {
                    $groupScopeHasParam = true;
                    break;
                }
            }
        }

        return
            $this->container->hasParameter($defaultScopeParamName)
            || $groupScopeHasParam
            || $this->container->hasParameter($relativeScopeParamName)
            || $this->container->hasParameter($globalScopeParamName);
    }

    /**
     * Returns value for $paramName, in $namespace.
     *
     * @param string $paramName The parameter name, without $prefix and the current scope (i.e. siteaccess name).
     * @param string $namespace Namespace for the parameter name. If null, the default namespace will be used.
     * @param string $scope The scope you need $paramName value for. It's typically the siteaccess name.
     *                      If null, the current siteaccess name will be used.
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     *
     * @return mixed
     */
    public function getParameter($paramName, $namespace = null, $scope = null)
    {
        $namespace = $namespace ?: $this->defaultNamespace;
        $scope = $scope ?: $this->defaultScope;
        $triedScopes = array();

        // Global scope
        $globalScopeParamName = "$namespace." . self::SCOPE_GLOBAL . ".$paramName";
        if ($this->container->hasParameter($globalScopeParamName)) {
            return $this->container->getParameter($globalScopeParamName);
        }
        $triedScopes[] = self::SCOPE_GLOBAL;
        unset($globalScopeParamName);

        // Relative scope, siteaccess wise
        $relativeScopeParamName = "$namespace.$scope.$paramName";
        if ($this->container->hasParameter($relativeScopeParamName)) {
            return $this->container->getParameter($relativeScopeParamName);
        }
        $triedScopes[] = $this->defaultScope;
        unset($relativeScopeParamName);

        // Relative scope, siteaccess group wise
        if (isset($this->groupsBySiteAccess[$scope])) {
            foreach ($this->groupsBySiteAccess[$scope] as $groupName) {
                $relativeScopeParamName = "$namespace.$groupName.$paramName";
                if ($this->container->hasParameter($relativeScopeParamName)) {
                    return $this->container->getParameter($relativeScopeParamName);
                }
            }
        }

        // Default scope
        $defaultScopeParamName = "$namespace." . self::SCOPE_DEFAULT . ".$paramName";
        if ($this->container->hasParameter($defaultScopeParamName)) {
            return $this->container->getParameter($defaultScopeParamName);
        }
        $triedScopes[] = $this->defaultNamespace;
        unset($defaultScopeParamName);

        // Undefined parameter
        switch ($this->undefinedStrategy) {
            case self::UNDEFINED_STRATEGY_NULL:
                return null;

            case self::UNDEFINED_STRATEGY_EXCEPTION:
            default:
                throw new ParameterNotFoundException($paramName, $namespace, $triedScopes);
        }
    }

    /**
     * Changes the default namespace to look parameter into.
     *
     * @param string $defaultNamespace
     */
    public function setDefaultNamespace($defaultNamespace)
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * @return string
     */
    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    public function getDefaultScope()
    {
        return $this->defaultScope;
    }

    public function setDefaultScope($scope)
    {
        $this->defaultScope = $scope;
    }
}
