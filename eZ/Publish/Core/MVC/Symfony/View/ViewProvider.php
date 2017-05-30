<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

interface ViewProvider
{
    /**
     * @return View|null
     */
    public function getView(View $view);
}
