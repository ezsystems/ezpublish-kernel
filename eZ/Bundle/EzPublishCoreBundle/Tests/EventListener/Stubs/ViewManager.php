<?php
/**
 * File containing the ViewManager class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;

/**
 * Stub class for SiteAccessAware ViewManager
 */
class ViewManager implements ViewManagerInterface, SiteAccessAware
{

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
    }

    public function renderContent(
        Content $content,
        $viewType = ViewManagerInterface::VIEW_TYPE_FULL,
        $parameters = array()
    )
    {
    }

    public function renderLocation(
        Location $location,
        $viewType = ViewManagerInterface::VIEW_TYPE_FULL,
        $parameters = array()
    )
    {
    }

    public function renderBlock( Block $block, $parameters = array() )
    {
    }

    public function renderContentView( ContentViewInterface $view, array $defaultParams = array() )
    {
    }
}
