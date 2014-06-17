<?php
/**
 * File containing the PageController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    private $viewManager;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService
     */
    protected $pageService;

    public function __construct( ViewManager $viewManager, PageService $pageService )
    {
        $this->viewManager = $viewManager;
        $this->pageService = $pageService;
    }

    /**
     * Render the block
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param array $params
     * @param array $cacheSettings settings for the HTTP cache, 'smax-age' and
     *        'max-age' are checked.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewBlock( Block $block, array $params = array(), array $cacheSettings = array() )
    {
        $response = new Response();
        if ( $this->getParameter( 'content.view_cache' ) === true )
        {
            $response->setPublic();
            if (
                isset( $cacheSettings['smax-age'] )
                && is_int( $cacheSettings['smax-age'] )
            )
            {
                $response->setSharedMaxAge( (int)$cacheSettings['smax-age'] );
            }
            if (
                isset( $cacheSettings['max-age'] )
                && is_int( $cacheSettings['max-age'] )
            )
            {
                $response->setMaxAge( (int)$cacheSettings['max-age'] );
            }
        }

        $response->setContent(
            $this->viewManager->renderBlock(
                $block,
                $params + array(
                    // @deprecated pageService injection will be removed in 6.0.
                    'pageService' => $this->pageService,
                    'valid_items' => $this->pageService->getValidBlockItems( $block )
                )
            )
        );
        return $response;
    }

    /**
     * Renders the block with given $id.
     *
     * This method can be used with ESI rendering strategy.
     *
     * @uses self::viewBlock()
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
    public function viewBlockById( $id, array $params = array(), array $cacheSettings = array() )
    {
        return $this->viewBlock(
            $this->pageService->loadBlock( $id ),
            $params,
            $cacheSettings
        );
    }
}
