<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

/**
 * Builds BlockView objects.
 */
class BlockViewBuilder implements ViewBuilder
{
    /** @var PageService */
    private $pageService;

    /** @var Configurator */
    private $viewConfigurator;

    /** @var ParametersInjector */
    private $viewParametersInjector;

    public function __construct(
        PageService $pageService,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector
    ) {
        $this->pageService = $pageService;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
    }

    public function matches($argument)
    {
        return strpos($argument, 'ez_page:') !== false;
    }

    public function buildView(array $parameters)
    {
        $view = new BlockView();

        if (isset($parameters['id'])) {
            $view->setBlock(
                $this->pageService->loadBlock($parameters['id'])
            );
        } elseif ($parameters['block'] instanceof Block) {
            $view->setBlock($parameters['block']);
        }

        $this->viewConfigurator->configure($view);
        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }
}
