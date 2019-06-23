<?php

/**
 * File containing the ViewManager class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;

/**
 * Stub class for SiteAccessAware ViewManager.
 */
class ViewManager implements ViewManagerInterface, SiteAccessAware
{
    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
    }

    public function renderContent(
        Content $content,
        $viewType = ViewManagerInterface::VIEW_TYPE_FULL,
        $parameters = []
    ) {
    }

    public function renderLocation(
        Location $location,
        $viewType = ViewManagerInterface::VIEW_TYPE_FULL,
        $parameters = []
    ) {
    }

    public function renderBlock(Block $block, $parameters = [])
    {
    }

    public function renderContentView(View $view, array $defaultParams = [])
    {
    }
}
