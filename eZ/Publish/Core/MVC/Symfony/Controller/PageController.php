<?php

/**
 * File containing the PageController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;

/**
 * This controller provides the block view feature.
 *
 * @since 6.0.0 All methods except `viewAction()` are deprecated and will be removed in the future.
 */
class PageController extends Controller
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Manager */
    private $viewManager;

    /** @var \eZ\Publish\Core\FieldType\Page\PageService */
    protected $pageService;

    public function __construct(ViewManager $viewManager, PageService $pageService)
    {
        $this->viewManager = $viewManager;
        $this->pageService = $pageService;
    }

    /**
     * This is the default view action for a BlockView object.
     *
     * It doesn't do anything by itself: the returned View object is rendered by the ViewRendererListener
     * into an HttpFoundation Response.
     *
     * This action can be selectively replaced by a custom action by means of block_view
     * configuration. Custom actions can add parameters to the view and customize the Response the View will be
     * converted to. They may also bypass the ViewRenderer by returning an HttpFoundation Response.
     *
     * Cache is in both cases handled by the BlockCacheResponseListener.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\BlockView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\BlockView
     */
    public function viewAction(BlockView $view)
    {
        return $view;
    }

    /**
     * Render the block.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param array $params
     * @param array $cacheSettings settings for the HTTP cache, 'smax-age' and
     *        'max-age' are checked.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewBlock(Block $block, array $params = [], array $cacheSettings = [])
    {
        $response = new Response();
        if ($this->getParameter('content.view_cache') === true) {
            $response->setPublic();
            if (
                isset($cacheSettings['smax-age'])
                && is_int($cacheSettings['smax-age'])
            ) {
                $response->setSharedMaxAge((int)$cacheSettings['smax-age']);
            }
            if (
                isset($cacheSettings['max-age'])
                && is_int($cacheSettings['max-age'])
            ) {
                $response->setMaxAge((int)$cacheSettings['max-age']);
            }
        }

        $response->setContent(
            $this->viewManager->renderBlock(
                $block,
                $params + [
                    // @deprecated pageService injection will be removed in 6.0.
                    'pageService' => $this->pageService,
                    'valid_items' => $this->pageService->getValidBlockItems($block),
                ]
            )
        );

        return $response;
    }

    /**
     * Renders the block with given $id.
     *
     * This method can be used with ESI rendering strategy.
     *
     * @uses \self::viewBlock()
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If block could not be found.
     *
     * @param mixed $id Block id
     * @param array $params
     * @param array $cacheSettings settings for the HTTP cache, 'smax-age' and
     *              'max-age' are checked.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewBlockById($id, array $params = [], array $cacheSettings = [])
    {
        return $this->viewBlock(
            $this->pageService->loadBlock($id),
            $params,
            $cacheSettings
        );
    }
}
