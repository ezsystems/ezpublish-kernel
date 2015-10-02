<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\View;

class Registry
{
    /**
     * Array of ViewProvider, indexed by handled type.
     * @var \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[][]
     */
    private $viewProviders;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[]
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function getViewProviders(View $view)
    {
        foreach (array_keys($this->viewProviders) as $type) {
            if ($view instanceof $type) {
                return $this->viewProviders[$type];
            }
        }
        throw new InvalidArgumentException('view', 'No compatible ViewProvider found for ' . gettype($view));
    }

    /**
     * Sets the complete list of view providers.
     *
     * @param array $viewProviders ['type' => [ViewProvider1, ViewProvider2]]
     */
    public function setViewProviders(array $viewProviders)
    {
        $this->viewProviders = $viewProviders;
    }
}
