<?php
/**
 * File containing the ResourceRedirect class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

class ResourceRedirect
{
    public $redirectUri;

    public $resourceType;

    public function __construct( $redirectUri, $resourceType )
    {
        $this->redirectUri = $redirectUri;
        $this->resourceType = $resourceType;
    }
}
