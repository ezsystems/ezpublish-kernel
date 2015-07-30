<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParserInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\ExpressionLanguage\Expression;

class ComplexSettingsPass implements CompilerPassInterface
{
    /** @var ComplexSettingParserInterface */
    private $parser;

    public function __construct(ComplexSettingParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            $arguments = $definition->getArguments();
            foreach ($arguments as $argumentIndex => $argumentValue) {
                if (!is_string($argumentValue)) {
                    continue;
                }

                if (!$this->parser->containsDynamicSettings($argumentValue)) {
                    continue;
                }

                if ($this->parser->isDynamicSetting($argumentValue)) {
                    continue;
                }

                $arguments[$argumentIndex] = $this->createComplexSettingExpression(
                    $argumentValue,
                    $this->parser->parseComplexSetting($argumentValue)
                );
            }

            $definition->setArguments($arguments);
        }
    }

    /**
     * Creates an expression for given complex setting.
     *
     * The expression uses 'ezpublish.config.complex_setting_value.resolver' service for complex setting resolution.
     * The complex setting value resolver has a variable number of arguments.
     * Dynamic settings are added as tuples: first the argument without the leading and trailing $, so that it is not
     * transformed by the config resolver pass, then the argument as a string, so that it does get transformed.
     *
     * @param string $argumentValue The original argument ($var$/$another_var$)
     * @param array $dynamicSettings Array of dynamic settings in $argumentValue
     *
     * @return Expression
     */
    private function createComplexSettingExpression($argumentValue, array $dynamicSettings)
    {
        $resolverArguments = ['"' . $argumentValue . '"'];
        foreach ($dynamicSettings as $dynamicSetting) {
            // Trim the '$'  so that the dynamic setting doesn't get transformed
            $resolverArguments[] = '"' . trim($dynamicSetting, '$') . '"';
            // This one will be transformed
            $resolverArguments[] = $this->createConfigResolverSubExpression(
                $this->parser->parseDynamicSetting($dynamicSetting)
            );
        }

        $expression = sprintf(
            'service("ezpublish.config.complex_setting_value.resolver").resolveSetting(%s)',
            implode(', ', $resolverArguments)
        );

        return new Expression($expression);
    }

    /**
     * Returns the sub expression (as string) to resolve individual dynamic settings contained in original complex setting.
     *
     * @param array $parsedDynamicSetting Dynamic setting, parsed.
     *
     * @return string
     */
    private function createConfigResolverSubExpression(array $parsedDynamicSetting)
    {
        $expression = sprintf(
            'service("ezpublish.config.resolver").getParameter("%s", %s, %s)',
            $parsedDynamicSetting['param'],
            isset($parsedDynamicSetting['namespace']) ? '"' . $parsedDynamicSetting['namespace'] . '"' : 'null',
            isset($parsedDynamicSetting['scope']) ? '"' . $parsedDynamicSetting['scope'] . '"' : 'null'
        );

        return $expression;
    }
}
