<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

interface OutputRenderer
{
    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $view
     * @param array $params
     *
     * @return string
     */
    public function render(ContentViewInterface $view, array $params);
}
