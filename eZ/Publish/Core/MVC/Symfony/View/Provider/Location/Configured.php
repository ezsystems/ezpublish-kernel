<?php

/**
 * File containing the View\Provider\Location\Configured class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Provider\Location;

use eZ\Publish\Core\MVC\Symfony\View\Provider\Configured as BaseConfigured;
use eZ\Publish\Core\MVC\Symfony\View\Provider\Location as LocationViewProvider;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;

class Configured extends BaseConfigured implements LocationViewProvider
{
    /**
     * Returns a ContentView object corresponding to $location, or null if not applicable.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|null
     */
    public function getView(Location $location, $viewType = ViewManagerInterface::VIEW_TYPE_FULL)
    {
        $viewConfig = $this->matcherFactory->match($location, $viewType);
        if (empty($viewConfig)) {
            return;
        }

        return $this->buildContentView($viewConfig);
    }
}
