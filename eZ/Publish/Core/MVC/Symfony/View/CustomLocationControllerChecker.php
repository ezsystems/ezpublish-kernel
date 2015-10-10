<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Used to check if a Location is rendered using a custom controller.
 */
class CustomLocationControllerChecker
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[] */
    private $viewProviders;

    /**
     * Tests if $location has match a view that uses a custom controller.
     *
     * @since 5.4.5
     *
     * @param $content Content
     * @param $location Location
     * @param $viewMode string
     *
     * @return bool
     */
    public function usesCustomController(Content $content, Location $location, $viewMode = 'full')
    {
        $contentView = new ContentView(null, [], $viewMode);
        $contentView->setContent($content);
        $contentView->setLocation($location);

        foreach ($this->viewProviders as $viewProvider) {
            $view = $viewProvider->getView($contentView);
            if ($view instanceof View) {
                if ($view->getControllerReference() !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $viewProviders \eZ\Publish\Core\MVC\Symfony\View\ViewProvider[]
     */
    public function addViewProviders(array $viewProviders)
    {
        $this->viewProviders = $viewProviders;
    }
}
