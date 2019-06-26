<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector\Block;

use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects valid items into the block view.
 */
class ValidItems implements EventSubscriberInterface
{
    /** @var PageService */
    protected $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectValidItems'];
    }

    public function injectValidItems(FilterViewParametersEvent $event)
    {
        $view = $event->getView();
        if ($view instanceof BlockView) {
            $event->getParameterBag()->set(
                'valid_items',
                $this->pageService->getValidBlockItems($view->getBlock())
            );
        }
    }
}
