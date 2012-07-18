<?php
/**
 * File containing the ContentViewProvider interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\View;

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
     * @return \eZ\Publish\MVC\View\ContentView|void
     */
    public function getViewForContent( ContentInfo $contentInfo );

    /**
     * Returns a ContentView object corresponding to $location, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return \eZ\Publish\MVC\View\ContentView|void
     */
    public function getViewForLocation( Location $location );
}
