<?php
/**
 * File containing the View\Provider\Location interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\Provider;

use eZ\Publish\API\Repository\Values\Content\Location as APIContentLocation;

/**
 * Interface for location view providers.
 *
 * Location view providers select a view for a given location, depending on its own internal rules.
 */
interface Location
{
    /**
     * Returns a ContentView object corresponding to $location, or void if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|void
     */
    public function getView( APIContentLocation $location, $viewType );
}
