<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder\Registry;

use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilderRegistry;

/**
 * A registry of ViewBuilders that uses the ViewBuilder's match() method to identify the builder against
 * a controller string.
 */
class ControllerMatch implements ViewBuilderRegistry
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder[] */
    private $registry = [];

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder[] $viewBuilders
     */
    public function addToRegistry(array $viewBuilders)
    {
        $this->registry = array_merge($this->registry, $viewBuilders);
    }

    /**
     * Returns the ViewBuilder that matches the given controller string.
     *
     * @param string $controllerString A controller string to match against. Example: ez_content:viewAction.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder|null
     */
    public function getFromRegistry($controllerString)
    {
        if (!is_string($controllerString)) {
            return null;
        }

        foreach ($this->registry as $viewBuilder) {
            if ($viewBuilder->matches($controllerString)) {
                return $viewBuilder;
            }
        }

        return null;
    }
}
