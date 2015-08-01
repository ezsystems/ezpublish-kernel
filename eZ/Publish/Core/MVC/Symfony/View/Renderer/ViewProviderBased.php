<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Renderer;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;
use eZ\Publish\Core\MVC\Symfony\View\OutputRenderer;
use eZ\Publish\Core\MVC\Symfony\View\ViewProvider;
use eZ\Publish\Core\MVC\Symfony\View\ViewRenderer;

/**
 * Base for ViewRenderers based on a ViewProvider (and OutputRenderer, as a matter of fact).
 */
abstract class ViewProviderBased implements ViewRenderer
{
    /** @var array */
    protected $viewProviders;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\OutputRenderer */
    protected $outputRenderer;

    public function __construct(array $viewProviders = [], OutputRenderer $outputRenderer)
    {
        $this->viewProviders = $viewProviders;
        $this->outputRenderer = $outputRenderer;
    }

    public function addViewProvider($viewProvider)
    {
        $this->viewProviders[] = $viewProvider;
    }

    /**
     * Renders
     *
     * @param ValueObject $value
     * @param string $viewType
     * @param array $params
     *
     * @return mixed
     */
    public function render($value, $viewType = ViewRenderer::VIEW_TYPE_FULL, $params = [])
    {
        foreach ($this->getViewProviders() as $viewProvider) {
            $view = $viewProvider->getView($value, $viewType);
            if ($view instanceof ContentViewInterface) {
                $this->filterRenderingParameters($value, $params);

                return $this->outputRenderer->render($view, $params);
            }
        }

        throw new \RuntimeException("Unable to find a template for Value (@todo add the value or its type)");
    }

    /**
     * Filters $params before it is sent to the OutputRenderer.
     * Used to complete what parameters are sent to rendering, based on the rendered ValueObject and current parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $valueObject
     * @param array $params

     *
     * @return void
     */
    abstract protected function filterRenderingParameters(ValueObject $valueObject, array &$params);

    /**
     * @return ViewProvider[]
     */
    protected function getViewProviders()
    {
        // @todo sort
        return $this->viewProviders;
    }
}
