<?php

/**
 * File containing the ConfigResolverParameterPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

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
    /** @var DynamicSettingParserInterface */
    private $dynamicSettingParser;

    public function __construct(DynamicSettingParserInterface $dynamicSettingParser)
    {
        $this->dynamicSettingParser = $dynamicSettingParser;
    }

    public function process(ContainerBuilder $container)
    {
        $dynamicSettingsServices = [];
        $resettableServices = [];
        $updateableServices = $container->getParameter('ezpublish.config_resolver.updateable_services');
        // Pass #1 Loop against all arguments of all service definitions to replace dynamic settings by the fake service.
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            // Constructor injection
            $replaceArguments = [];
            foreach ($definition->getArguments() as $i => $arg) {
                if (!$this->dynamicSettingParser->isDynamicSetting($arg)) {
                    continue;
                }

                // Decorators use index_X for their key
                if (strpos($i, 'index') === 0) {
                    $i = (int)substr($i, strlen('index_'));
                }

                $replaceArguments[$i] = $this->createExpression($this->dynamicSettingParser->parseDynamicSetting($arg));
            }

            if (!empty($replaceArguments)) {
                $dynamicSettingsServices[$serviceId] = true;
                foreach ($replaceArguments as $i => $arg) {
                    $definition->replaceArgument($i, $arg);
                }
            }

            // Setter injection
            $methodCalls = $definition->getMethodCalls();
            foreach ($methodCalls as $i => &$call) {
                list($method, $callArgs) = $call;
                $callHasDynamicSetting = false;
                foreach ($callArgs as &$callArg) {
                    if (!$this->dynamicSettingParser->isDynamicSetting($callArg)) {
                        continue;
                    }

                    $callArg = $this->createExpression($this->dynamicSettingParser->parseDynamicSetting($callArg));
                    $callHasDynamicSetting = true;
                }

                $call = [$method, $callArgs];
                if ($callHasDynamicSetting) {
                    // We only support single dynamic setting injection for updatable services.
                    if (count($callArgs) == 1) {
                        $updateableServices[$serviceId][] = [$method, (string)$callArgs[0]];
                    } else {
                        // Method call has more than 1 argument, so we will reset it instead of updating it.
                        $dynamicSettingsServices[$serviceId] = true;
                        // Ensure current service is not registered as updatable service.
                        unset($updateableServices[$serviceId]);
                    }
                }
            }

            $definition->setMethodCalls($methodCalls);
        }

        // Pass #2 Loop again, to get all services depending on dynamic settings services.
        foreach ($container->getDefinitions() as $id => $definition) {
            $isDependent = false;
            foreach ($definition->getArguments() as $arg) {
                if (
                    !(
                        $arg instanceof Reference
                        && isset($dynamicSettingsServices[(string)$arg])
                    )
                ) {
                    continue;
                }

                $isDependent = true;
                break;
            }

            if ($isDependent) {
                $resettableServices[] = $id;
            }
        }

        $resettableServices = array_unique(
            array_merge(
                array_keys($dynamicSettingsServices),
                $resettableServices
            )
        );
        $container->setParameter('ezpublish.config_resolver.resettable_services', $resettableServices);
        $container->setParameter('ezpublish.config_resolver.updateable_services', $updateableServices);
    }

    /**
     * Returns the expression object corresponding to passed dynamic setting.
     *
     * @param array $dynamicSetting Parsed dynamic setting, as returned by DynamicSettingParser.
     *
     * @return Expression
     */
    private function createExpression(array $dynamicSetting)
    {
        $expression = sprintf(
            'service("ezpublish.config.resolver").getParameter("%s", %s, %s)',
            $dynamicSetting['param'],
            isset($dynamicSetting['namespace']) ? '"' . $dynamicSetting['namespace'] . '"' : 'null',
            isset($dynamicSetting['scope']) ? '"' . $dynamicSetting['scope'] . '"' : 'null'
        );

        return new Expression($expression);
    }
}
