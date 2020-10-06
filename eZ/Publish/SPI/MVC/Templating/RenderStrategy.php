<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\Templating;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;

/**
 * Strategy to decide, based on ValueObject descendant type, which
 * renderer to pick (like RenderContentStrategy or else). To be used
 * mainly by rendering abstraction Twig helpers, but may be used to
 * inline rendering of Ibexa VOs anywhere.
 */
interface RenderStrategy
{
    public function supports(ValueObject $valueObject): bool;

    public function render(ValueObject $valueObject, RenderOptions $options): string;
}
