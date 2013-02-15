<?php
/**
 * File containing the TemporaryRedirect class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\Core\REST\Common\Value as RestValue;

class TemporaryRedirect extends RestValue
{
    /**
     * Redirect URI
     *
     * @var string
     */
    public $redirectUri;

    /**
     * Construct
     *
     * @param string $redirectUri
     */
    public function __construct( $redirectUri )
    {
        $this->redirectUri = $redirectUri;
    }
}
