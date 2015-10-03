<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

/**
 * A simple registry of ViewBuilders that uses the ViewBuilder's match() method to identify the builder.
 */
class ViewBuilderRegistry
{
    /** @var ViewBuilder[] */
    private $viewBuilders;

    /**
     * @param ViewBuilder[] $viewBuilders
     */
    public function __construct(array $viewBuilders = [])
    {
        $this->viewBuilders = $viewBuilders;
    }

    /**
     * @return ViewBuilder|null
     */
    public function get($argument)
    {
        foreach ($this->viewBuilders as $viewBuilder) {
            if ($viewBuilder->matches($argument)) {
                return $viewBuilder;
            }
        }

        return null;
    }
}
