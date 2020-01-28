<?php

/**
 * File containing the ConfigResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
class ConfigResolver implements VersatileScopeInterface, SiteAccessAware, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const SCOPE_GLOBAL = 'global';
    const SCOPE_DEFAULT = 'default';

    const UNDEFINED_STRATEGY_EXCEPTION = 1;
    const UNDEFINED_STRATEGY_NULL = 2;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    protected $siteAccess;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var array Siteaccess groups, indexed by siteaccess name */
    protected $groupsBySiteAccess;

    /** @var string */
    protected $defaultNamespace;

    /** @var string */
    protected $defaultScope;

    /** @var int */
    protected $undefinedStrategy;

    /** @var array[] List of blame => [params] loaded while siteAccess->matchingType was 'uninitialized' */
    private $tooEarlyLoadedList = [];

    /**
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param array $groupsBySiteAccess SiteAccess groups, indexed by siteaccess.
     * @param string $defaultNamespace The default namespace
     * @param int $undefinedStrategy Strategy to use when encountering undefined parameters.
     *                               Must be one of
     *                                  - ConfigResolver::UNDEFINED_STRATEGY_EXCEPTION (throw an exception)
     *                                  - ConfigResolver::UNDEFINED_STRATEGY_NULL (return null)
     */
    public function __construct(
        ?LoggerInterface $logger,
        array $groupsBySiteAccess,
        $defaultNamespace,
        $undefinedStrategy = self::UNDEFINED_STRATEGY_EXCEPTION
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->groupsBySiteAccess = $groupsBySiteAccess;
        $this->defaultNamespace = $defaultNamespace;
        $this->undefinedStrategy = $undefinedStrategy;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
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

    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool
    {
        $namespace = $namespace ?: $this->defaultNamespace;
        $scope = $scope ?: $this->getDefaultScope();

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
     * @return mixed
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     */
    public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null)
    {
        $this->logTooEarlyLoadedListIfNeeded($paramName);

        $namespace = $namespace ?: $this->defaultNamespace;
        $scope = $scope ?: $this->getDefaultScope();
        $triedScopes = [];

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
        $triedScopes[] = $scope;
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

    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    public function getDefaultScope(): string
    {
        return $this->defaultScope ?: $this->siteAccess->name;
    }

    /**
     * @param string $scope The default "scope" aka siteaccess name, as opposed to the self::SCOPE_DEFAULT.
     */
    public function setDefaultScope(string $scope): void
    {
        $this->defaultScope = $scope;

        // On scope change check if siteaccess has been updated so we can log warnings if there are any
        if ($this->siteAccess->matchingType !== 'uninitialized') {
            $this->warnAboutTooEarlyLoadedParams();
        }
    }

    private function warnAboutTooEarlyLoadedParams()
    {
        if (empty($this->tooEarlyLoadedList)) {
            return;
        }

        foreach ($this->tooEarlyLoadedList as $blame => $params) {
            $this->logger->warning(sprintf(
                'ConfigResolver was used by "%s" before SiteAccess was initialized, loading parameter(s) '
                . '%s. As this can cause very hard to debug issues, '
                . 'try to use ConfigResolver lazily, '
                . (PHP_SAPI === 'cli' ? 'make the affected commands lazy, ' : '')
                . 'make the service lazy or see if you can inject another lazy service.',
                $blame,
                '"$' . implode('$", "$', array_unique($params)) . '$"'
            ));
        }

        $this->tooEarlyLoadedList = [];
    }

    /**
     * If in run-time debug mode, before SiteAccess is initialized, log getParameter() usages when considered "unsafe".
     *
     * @return string
     */
    private function logTooEarlyLoadedListIfNeeded($paramName)
    {
        if ($this->container instanceof ContainerBuilder) {
            return;
        }

        if ($this->siteAccess->matchingType !== 'uninitialized') {
            return;
        }

        // So we are in a state where we need to warn about unsafe use of config resolver parameters...
        // .. it's a bit costly to do so, so we only do it in debug mode
        $container = $this->container;
        if (!$container->getParameter('kernel.debug')) {
            return;
        }

        $serviceName = '??';
        $firstService = '??';
        $commandName = null;
        $resettableServiceIds = $container->getParameter('ezpublish.config_resolver.resettable_services');
        $updatableServices = $container->getParameter('ezpublish.config_resolver.updateable_services');

        // Lookup trace to find last service being loaded as possible blame for eager loading
        // Abort if one of the earlier services is detected to be "safe", aka updatable
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 35) as $t) {
            if (!isset($t['function']) || $t['function'] === 'getParameter' || $t['function'] === __FUNCTION__) {
                continue;
            }

            // Extract service name from first service matching getXXService pattern
            if (\strpos($t['function'], 'get') === 0 && \strrpos($t['function'], 'Service') === \strlen($t['function']) - 7) {
                $potentialClassName = \substr($t['function'], 3, -7);
                $serviceName = \strtolower(\preg_replace('/\B([A-Z])/', '_$1', \str_replace('_', '.', $potentialClassName)));

                // This (->setter('$dynamic_param$')) is safe as the system is able to update it on scope changes, abort
                if (isset($updatableServices[$serviceName])) {
                    return;
                }

                // !! The remaining cases are most likely "not safe", typically:
                // - ctor('$dynamic_param$') => this should be avoided, use setter or use config resolver instead
                // - config resolver use in service factory => the service (or decorator, if any) should be marked lazy

                // Possible exception: Class name based services, can't be resolved as namespace is omitted from
                // compiled function. In this case we won't know if it was updateable and "safe", so we warn to be sure
                if (!in_array($serviceName, $resettableServiceIds, true) && !$container->has($serviceName)) {
                    $serviceName = $potentialClassName;
                } else {
                    $serviceName = '@' . $serviceName;
                }

                // Keep track of the first service loaded
                if ($firstService === '??') {
                    $firstService = $serviceName;
                }

                // Detect if we found the command loading the service, if we track that as lasts service
                if (PHP_SAPI === 'cli' && isset($t['file']) && \stripos($t['file'], 'CommandService.php') !== false) {
                    $path = explode(DIRECTORY_SEPARATOR, $t['file']);
                    $commandName = \substr($path[count($path) - 1], 3, -11);
                    break;
                }
            }
        }

        // Skip service name if same as first service
        if ($serviceName === $firstService) {
            $serviceName = '';
        }

        // Add command name if present as the trigger
        if ($commandName) {
            $blame = "$commandName($serviceName) -> $firstService";
        } else {
            $blame = ($serviceName ? $serviceName . ' -> ' : '') . $firstService;
        }
        $this->tooEarlyLoadedList[$blame][] = $paramName;
    }
}
