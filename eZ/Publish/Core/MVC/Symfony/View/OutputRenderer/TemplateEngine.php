<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View\OutputRenderer;

use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;
use eZ\Publish\Core\MVC\Symfony\View\OutputRenderer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;

class TemplateEngine implements OutputRenderer
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    public function __construct(
        EngineInterface $templateEngine,
        EventDispatcherInterface $eventDispatcher,
        $viewBaseLayout
    ) {
        $this->templateEngine = $templateEngine;
        $this->eventDispatcher = $eventDispatcher;
        $this->viewBaseLayout = $viewBaseLayout;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $view
     * @param array $defaultParams
     *
     * @return string
     */
    public function render(ContentViewInterface $view, array $defaultParams)
    {
        $defaultParams['viewbaseLayout'] = $this->viewBaseLayout;
        $view->addParameters($defaultParams);
        $this->eventDispatcher->dispatch(
            MVCEvents::PRE_CONTENT_VIEW,
            new PreContentViewEvent($view)
        );

        $templateIdentifier = $view->getTemplateIdentifier();
        $params = $view->getParameters();
        if ($templateIdentifier instanceof \Closure) {
            return $templateIdentifier($params);
        }

        return $this->templateEngine->render($templateIdentifier, $params);
    }
}
