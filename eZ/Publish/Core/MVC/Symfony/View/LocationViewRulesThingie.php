<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 */
class LocationViewRulesThingie
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Provider\Location[] */
    private $viewProviders;

    /**
     * Tests if $location has match a view that uses a custom controller.
     *
     * @since 5.4.5
     *
     * @param $location Location
     *
     * @return bool
     */
    public function usesCustomController(Location $location, $viewMode = 'full')
    {
        foreach ($this->viewProviders as $viewProvider) {
            $view = $viewProvider->getView($location, $viewMode);
            if ($view instanceof ContentViewInterface) {
                $configHash = $view->getConfigHash();
                if (isset($configHash['controller'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $viewProviders \eZ\Publish\Core\MVC\Symfony\View\Provider\Location[]
     */
    public function addViewProviders(array $viewProviders)
    {
        $this->viewProviders = $viewProviders;
    }
}
