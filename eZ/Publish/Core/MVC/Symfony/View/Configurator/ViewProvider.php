<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Configurator;

use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Registry;
use eZ\Publish\Core\MVC\Symfony\View\View;

/**
 * Configures a view based on the ViewProviders.
 *
 * Typically, the Configured ViewProvider will be included, meaning that Views will be customized based on the
 * view rules defined in the siteaccess aware configuration (content_view, block_view, ...).
 */
class ViewProvider implements Configurator
{
    /** @var Registry */
    private $providerRegistry;

    /**
     * ViewProvider constructor.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\Provider\Registry $providersRegistry
     */
    public function __construct(Registry $providersRegistry)
    {
        $this->providerRegistry = $providersRegistry;
    }

    public function configure(View $view)
    {
        foreach ($this->providerRegistry->getViewProviders($view) as $viewProvider) {
            if ($view2 = $viewProvider->getView($view)) {
                $configHash = $view2->getConfigHash();
                $view->setConfigHash($configHash);
                if (($templateIdentifier = $view2->getTemplateIdentifier()) !== null) {
                    $view->setTemplateIdentifier($templateIdentifier);
                }

                if (($controllerReference = $view2->getControllerReference()) !== null) {
                    $view->setControllerReference($controllerReference);
                }

                if (isset($configHash['params']) && is_array($configHash['params'])) {
                    $view->addParameters($configHash['params']);
                }

                $view->addParameters($view2->getParameters());

                return;
            }
        }
    }
}
