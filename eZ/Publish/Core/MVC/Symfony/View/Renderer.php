<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

/**
 * Renders a View to a string representation.
 */
interface Renderer
{
    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return string
     */
    public function render(View $view);
}
