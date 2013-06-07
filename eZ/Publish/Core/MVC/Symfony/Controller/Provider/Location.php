<?php
/**
 * File containing the Location interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Provider;

use eZ\Publish\API\Repository\Values\Content\Location as APIContentLocation;

/**
 * Interface for location controller providers.
 *
 * Location controller providers select a controller for a given location, depending on its own internal rules.
 */
interface Location
{
    /**
     * Returns a ControllerReference object corresponding to $location, or null if not applicable
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType Variation of display for your content.
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference|null
     */
    public function getController( APIContentLocation $location, $viewType );
}