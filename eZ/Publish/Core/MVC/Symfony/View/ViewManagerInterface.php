<?php

/**
 * File containing the ViewManagerInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

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
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderContent(Content $content, $viewType = self::VIEW_TYPE_FULL, $parameters = []);

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
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderLocation(Location $location, $viewType = self::VIEW_TYPE_FULL, $parameters = []);

    /**
     * Renders $block by selecting the right template.
     * $block will be injected in the selected template.
     *
     * @param \eZ\Publish\Core\FieldType\Page\Parts\Block $block
     * @param array $parameters Parameters to pass to the template called to
     *        render the view. By default, it's empty.
     *        'block' entry is reserved for the Block that is viewed.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function renderBlock(Block $block, $parameters = []);

    /**
     * Renders passed ContentView object via the template engine.
     * If $view's template identifier is a closure, then it is called directly and the result is returned as is.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface $view
     * @param array $defaultParams
     *
     * @return string
     */
    public function renderContentView(View $view, array $defaultParams = []);
}
