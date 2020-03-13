<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\QueryRenderingExtension;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final class QueryRenderingExtensionTest extends FileSystemTwigIntegrationTestCase
{
    protected function getExtensions(): array
    {
        $fragmentHandler = $this->createMock(FragmentHandler::class);
        $fragmentHandler
            ->method('render')
            ->willReturnCallback(static function (...$args): string {
                return var_export($args, true);
            });

        return [
            new QueryRenderingExtension($fragmentHandler),
        ];
    }

    protected function getFixturesDir(): string
    {
        return __DIR__ . '/_fixtures/query_rendering_functions/';
    }
}
