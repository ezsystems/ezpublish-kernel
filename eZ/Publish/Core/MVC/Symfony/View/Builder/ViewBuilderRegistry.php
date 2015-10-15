<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

/**
 * A simple registry of ViewBuilders that uses the ViewBuilder's match() method to identify the builder.
 */
interface ViewBuilderRegistry
{
    /**
     * Returns the ViewBuilder matching the argument.
     *
     * @param mixed $argument
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder|null The ViewBuilder, or null if there's none.
     */
    public function getFromRegistry($argument);

    /**
     * Adds ViewBuilders from the $objects array to the registry.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder[] $objects
     */
    public function addToRegistry(array $objects);
}
