<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Bundle\EzPublishCoreBundle\FieldType\Page\PageService as CoreBundlePageService;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Injects valid ContentInfo items into the block view.
 */
class BlockValidContentInfoItemsListener implements EventSubscriberInterface
{
    /** @var PageService */
    protected $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectValidContentInfoItems'];
    }

    public function injectValidContentInfoItems(FilterViewParametersEvent $event)
    {
        $view = $event->getView();
        if ($view instanceof BlockView && $this->pageService instanceof CoreBundlePageService) {
            $event->getParameterBag()->set(
                'valid_contentinfo_items',
                $this->pageService->getValidBlockItemsAsContentInfo($view->getBlock())
            );
        }
    }
}
