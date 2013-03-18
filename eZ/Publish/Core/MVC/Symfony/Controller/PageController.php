<?php
/**
 * File containing the PageController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    private $viewManager;

    public function __construct( ViewManager $viewManager )
    {
        $this->viewManager = $viewManager;
    }

    /**
     * Render the block
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param array $parameters
     * @param array $cacheSettings settings for the HTTP cache, 'smax-age' and
     *        'max-age' are checked.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewBlock( Block $block, array $parameters = array(), array $cacheSettings = array() )
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
            $this->viewManager->renderBlock( $block, $parameters )
        );
        return $response;
    }
}
