<?php
/**
 * File containing the ContentViewProvider interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Interface for content view providers.
 *
 * Content view providers select a view for a given content/location, depending on its own internal rules.
 */
interface ContentViewProvider
{
    /**
     * Returns a ContentView object corresponding to $contentInfo, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $viewType Variation of display for your content
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getViewForContent( ContentInfo $contentInfo, $viewType );

    /**
     * Returns a ContentView object corresponding to $location, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getViewForLocation( Location $location, $viewType );
}
