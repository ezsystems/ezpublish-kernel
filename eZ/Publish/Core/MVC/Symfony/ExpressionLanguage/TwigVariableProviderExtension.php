<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\ExpressionLanguage;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\VariableProviderRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class TwigVariableProviderExtension implements ExpressionFunctionProviderInterface
{
    public const PROVIDER_REGISTRY_PARAMETER = 'providerRegistry';
    public const VIEW_PARAMETER = 'view';

    /**
     * @return \Symfony\Component\ExpressionLanguage\ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'provider',
                function (string $identifier) {
                    return 'Not implemented: Not a Dependency Injection expression';
                },
                function (array $variables, string $identifier) {
                    if (!$this->hasParameterProvider($variables)) {
                        throw new InvalidArgumentException(
                            self::PROVIDER_REGISTRY_PARAMETER,
                            'Expression parameter is not a valid type of ' . VariableProviderRegistry::class
                        );
                    }

                    $view = $variables[self::VIEW_PARAMETER] ?? new ContentView();
                    $providerRegistry = $variables[self::PROVIDER_REGISTRY_PARAMETER];

                    $provider = $providerRegistry->getTwigVariableProvider($identifier);

                    return !empty($provider)
                        ? $provider->getTwigVariables($view, $variables)
                        : [];
                }
            ),
        ];
    }

    private function hasParameterProvider(array $variables): bool
    {
        return !empty($variables[self::PROVIDER_REGISTRY_PARAMETER])
            && $variables[self::PROVIDER_REGISTRY_PARAMETER] instanceof VariableProviderRegistry;
    }
}
