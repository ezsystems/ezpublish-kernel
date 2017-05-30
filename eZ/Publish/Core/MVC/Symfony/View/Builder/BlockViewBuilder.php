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
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

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
    /**
     * @var string
     */
    private $viewClassFullName;

    public function __construct(
        PageService $pageService,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        $viewClassFullName = null
    ) {
        $this->pageService = $pageService;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->viewClassFullName = BlockView::class;
        if($viewClassFullName)
        {
            $viewReflectCLass = new \ReflectionClass($viewClassFullName);
            $view2 = $viewReflectCLass->newInstanceWithoutConstructor();
            if(!($view2 instanceof BlockView))
            {
                throw new InvalidArgumentException('viewClassFullName', "View class does not extend: "
                    . BlockView::class);
            }
            $this->viewClassFullName = $viewClassFullName;
        }
    }

    /**
     * @return string
     */
    public function getViewClassFullName()
    {
        return $this->viewClassFullName;
    }


    public function matches($argument)
    {
        return strpos($argument, 'ez_page:') !== false;
    }

    public function buildView(array $parameters)
    {
        $viewClassFullName = $this->getViewClassFullName();
        /** @var \eZ\Publish\Core\MVC\Symfony\View\BlockView $view */
        $view = new $viewClassFullName();

        if (isset($parameters['id'])) {
            $view->setBlock(
                $this->pageService->loadBlock($parameters['id'])
            );
        } elseif ($parameters['block'] instanceof Block) {
            $view->setBlock($parameters['block']);
        }

        $this->viewConfigurator->configure($view);

        // deprecated controller actions are replaced with their new equivalent, viewAction
        if (!$view->getControllerReference() instanceof ControllerReference) {
            if (in_array($parameters['_controller'], ['ez_page:viewBlock', 'ez_page:viewBlockById'])) {
                $view->setControllerReference(new ControllerReference('ez_page:viewAction'));
            }
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }
}
