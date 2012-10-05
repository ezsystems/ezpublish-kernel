<?php
/**
 * File containing the PermanentRedirect class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

class PermanentRedirect extends RestValue
{
    /**
     * Redirect URI
     *
     * @var string
     */
    public $redirectUri;

    /**
     * Resource type
     *
     * @var string
     */
    public $resourceType;

    /**
     * Construct
     *
     * @param string $redirectUri
     * @param string $resourceType
     */
    public function __construct( $redirectUri, $resourceType )
    {
        $this->redirectUri = $redirectUri;
        $this->resourceType = $resourceType;
    }
}
