<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\Templating;

use Symfony\Component\HttpFoundation\Request;

/**
 * Abstracts extensibility point of adding new rendering
 * methods for various Ibexa value objects.
 */
interface RenderMethod
{
    /**
     * Arbitrary string to be used as an option value for
     * rendering abstraction Twig helpers, like:
     *     {{ ez_render_content(content, {
     *         method: "render_method_identifier"
     *     }) }}.
     */
    public function getIdentifier(): string;

    public function render(Request $request): string;
}
