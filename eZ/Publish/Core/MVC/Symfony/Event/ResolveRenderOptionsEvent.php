<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Event;

use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use Symfony\Contracts\EventDispatcher\Event;

final class ResolveRenderOptionsEvent extends Event
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions */
    private $renderOptions;

    public function __construct(
        RenderOptions $renderOptions
    ) {
        $this->renderOptions = $renderOptions;
    }

    public function getRenderOptions(): RenderOptions
    {
        return $this->renderOptions;
    }

    public function setRenderOptions(RenderOptions $renderOptions): void
    {
        $this->renderOptions = $renderOptions;
    }
}
