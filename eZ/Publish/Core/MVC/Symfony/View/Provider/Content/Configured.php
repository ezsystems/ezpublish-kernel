<?php

/**
 * File containing the View\Provider\Content\Configured class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Content;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as BaseConfigured;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Content as ContentViewProvider;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;

class Configured extends BaseConfigured implements ContentViewProvider
{
    /**
     * Returns a ContentView object corresponding to $contentInfo, or null if not applicable.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    public function getView(ContentInfo $contentInfo, Location $location = null, $viewType = ViewManagerInterface::VIEW_TYPE_FULL)
    {
        $viewConfig = $this->matcherFactory->match($contentInfo, $viewType);
        if (empty($viewConfig)) {
            return;
        }

        return $this->buildContentView($viewConfig);
    }
}
