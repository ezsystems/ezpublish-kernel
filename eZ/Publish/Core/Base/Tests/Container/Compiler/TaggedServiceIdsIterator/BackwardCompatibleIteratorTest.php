<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Base\Tests\Container\Compiler\TaggedServiceIdsIterator;

use eZ\Publish\Core\Base\Container\Compiler\TaggedServiceIdsIterator\BackwardCompatibleIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

final class BackwardCompatibleIteratorTest extends TestCase
{
    private const EXAMPLE_SERVICE_TAG = 'current_tag';
    private const EXAMPLE_DEPRECATED_SERVICE_TAG = 'deprecated_tag';

    /** @var \eZ\Publish\Core\Base\Tests\Container\Compiler\TaggedServiceIdsIterator\DeprecationErrorCollector */
    private $deprecationErrorCollector;

    /** @var \Symfony\Component\DependencyInjection\TaggedContainerInterface */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(TaggedContainerInterface::class);
        $this->container
            ->method('findTaggedServiceIds')
            ->willReturnMap([
                [
                    self::EXAMPLE_DEPRECATED_SERVICE_TAG,
                    [
                        'app.service.foo' => [
                            ['alias' => 'foo'],
                        ],
                    ],
                ],
                [
                    self::EXAMPLE_SERVICE_TAG,
                    [
                        'app.service.bar' => [
                            ['alias' => 'bar'],
                        ],
                        'app.service.baz' => [
                            ['alias' => 'baz'],
                        ],
                    ],
                ],
            ]);

        $this->deprecationErrorCollector = new DeprecationErrorCollector();
        $this->deprecationErrorCollector->register();
    }

    protected function tearDown(): void
    {
        $this->deprecationErrorCollector->unregister();
    }

    public function testGetIterator(): void
    {
        $iterator = new BackwardCompatibleIterator(
            $this->container,
            self::EXAMPLE_SERVICE_TAG,
            self::EXAMPLE_DEPRECATED_SERVICE_TAG
        );

        $this->assertEquals([
            'app.service.foo' => [
                ['alias' => 'foo'],
            ],
            'app.service.bar' => [
                ['alias' => 'bar'],
            ],
            'app.service.baz' => [
                ['alias' => 'baz'],
            ],
        ], iterator_to_array($iterator));

        $this->assertDeprecationError(sprintf(
            'Service tag `%s` is deprecated and will be removed in eZ Platform 4.0. Tag %s with `%s` instead.',
            self::EXAMPLE_DEPRECATED_SERVICE_TAG,
            'app.service.foo',
            self::EXAMPLE_SERVICE_TAG
        ));
    }

    private function assertDeprecationError(string $expectedMessage): void
    {
        foreach ($this->deprecationErrorCollector->getErrors() as $error) {
            if ($error['message'] === $expectedMessage) {
                return;
            }
        }

        $this->fail(sprintf(
            'Failed asserting that deprecation warning with message "%s" has been triggered',
            $expectedMessage
        ));
    }
}
