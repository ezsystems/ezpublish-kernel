<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Renderer;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeResult;
use eZ\Publish\Core\MVC\Symfony\View\ViewRenderer;

class ContentQuery implements ViewRenderer
{
    /**
     * @var array
     */
    private $viewProviders;

    public function __construct(array $viewProviders = [])
    {
        $this->viewProviders = $viewProviders;
    }

    public function render($contentQueryResult, $viewType = self::VIEW_TYPE_FULL, $params = [])
    {
        foreach ($this->getViewProviders() as $viewProvider) {
            $view = $viewProvider->getView($contentQueryResult, $viewType);
            if ($view instanceof ContentQueryResultViewInterface) {
                $parameters[''] = $content;

                return $this->renderContentView($view, $parameters);
            }
        }

        throw new RuntimeException("Unable to find a template for #$contentInfo->id");
    }

    /**
     * Tests if the ViewRenderer can render $value.
     * @return bool true if the ViewRenderer can render $value
     */
    public function canRender($value)
    {
        return
            $value instanceof QueryTypeResult &&
            $value->query instanceof Query &&
            !$value->query instanceof LocationQuery;
    }

    /**
     * @return ViewProvider[]
     */
    private function getViewProviders()
    {
        // @todo sort
        return $this->viewProviders;
    }
}
