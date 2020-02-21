<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\View\Tests;

use ArrayIterator;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\View\GenericVariableProviderRegistry;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\SPI\MVC\View\VariableProvider;
use PHPUnit\Framework\TestCase;

final class VariableProviderRegistryTest extends TestCase
{
    private function getRegistry(array $providers): GenericVariableProviderRegistry
    {
        return new GenericVariableProviderRegistry(
            new ArrayIterator($providers)
        );
    }

    private function getProvider(string $identifier): VariableProvider
    {
        return new class($identifier) implements VariableProvider {
            private $identifier;

            public function __construct(string $identifier)
            {
                $this->identifier = $identifier;
            }

            public function getIdentifier(): string
            {
                return $this->identifier;
            }

            public function getTwigVariables(View $view, array $options = []): object
            {
                return (object)[
                    $this->identifier . '_parameter' => $this->identifier . '_value',
                ];
            }
        };
    }

    public function testParameterProviderGetter(): void
    {
        $registry = $this->getRegistry([
            $this->getProvider('provider_a'),
            $this->getProvider('provider_b'),
        ]);

        $providerA = $registry->getTwigVariableProvider('provider_a');
        $providerB = $registry->getTwigVariableProvider('provider_b');

        $this->assertEquals($providerA->getIdentifier(), 'provider_a');
        $this->assertEquals($providerB->getIdentifier(), 'provider_b');
    }

    public function testParameterNotFoundProviderGetter(): void
    {
        $this->expectException(NotFoundException::class);

        $registry = $this->getRegistry([
            $this->getProvider('provider_a'),
            $this->getProvider('provider_b'),
        ]);

        $registry->getTwigVariableProvider('provider_c');
    }

    public function testParameterProviderSetter(): void
    {
        $registry = $this->getRegistry([
            $this->getProvider('provider_a'),
            $this->getProvider('provider_b'),
        ]);

        $hasProviderC = $registry->hasTwigVariableProvider('provider_c');

        $this->assertFalse($hasProviderC);

        $registry->setTwigVariableProvider($this->getProvider('provider_c'));

        $providerC = $registry->getTwigVariableProvider('provider_c');
        $this->assertEquals($providerC->getIdentifier(), 'provider_c');
    }

    public function testParameterProviderChecker(): void
    {
        $registry = $this->getRegistry([
            $this->getProvider('provider_a'),
            $this->getProvider('provider_b'),
        ]);

        $this->assertTrue($registry->hasTwigVariableProvider('provider_a'));
        $this->assertTrue($registry->hasTwigVariableProvider('provider_b'));
        $this->assertFalse($registry->hasTwigVariableProvider('provider_c'));
    }
}
