<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\RenderMethod;

use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 */
final class RenderSubRequest implements RenderMethod
{
    public const IDENTIFIER = 'subrequest';

    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function __construct(
        KernelInterface $kernel
    ) {
        $this->kernel = $kernel;
    }

    public function render(Request $request): string
    {
        $response = $this->kernel->handle($request);

        return $response->getContent();
    }
}
