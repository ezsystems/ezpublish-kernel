<?php
/**
 * File containing the ViewManagerInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;

interface ViewManagerInterface
{
    const VIEW_TYPE_FULL = 'full';
    const VIEW_TYPE_LINE = 'line';

    /**
     * Renders $content by selecting the right template.
     * $content will be injected in the selected template.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $viewType Variation of display for your content. Default is 'full'.
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty. 'content' entry is
     *        reserved for the Content that is rendered.
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderContent( Content $content, $viewType = ViewManagerInterface::VIEW_TYPE_FULL, $parameters = array() );

    /**
     * Renders $location by selecting the right template for $viewType.
     * $content and $location will be injected in the selected template.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content. Default is 'full'.
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty. 'location' and 'content'
     *        entries are reserved for the Location (and its Content) that is
     *        viewed.
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderLocation( Location $location, $viewType = ViewManagerInterface::VIEW_TYPE_FULL, $parameters = array() );

    /**
     * Renders $block by selecting the right template.
     * $block will be injected in the selected template.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty.
     *        'block' entry is reserved for the Block that is viewed.
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderBlock( Block $block, $parameters = array() );

    /**
     * Renders passed ContentView object via the template engine.
     * If $view's template identifier is a closure, then it is called directly and the result is returned as is.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $view
     * @param array $defaultParams
     *
     * @return string
     */
    public function renderContentView( ContentViewInterface $view, array $defaultParams = array() );
}
