<?php
/**
 * File containing the LegacyRedirectResponse class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class LegacyRedirectResponse
 *
 * An extend of the Symfony RedirectResponse class with header cleanup features.
 *
 */
class LegacyRedirectResponse extends RedirectResponse
{
    /**
     * Sets the response headers from an array of legacy headers.
     *
     * The array of legacy headers reads as follow: array('X-Foo: Bar', ...)
     *
     * @param array $headers Array of legacy headers.
     */
    public function setLegacyHeaders( array $headers )
    {
        LegacyResponseManager::updateLegacyHeaders( $this, $headers );
    }

    /**
     * Wraps header_remove() function.
     * This is mainly to isolate it and become testable.
     *
     * @param string $headerName
     */
    public function removeHeader( $headerName )
    {
        header_remove( $headerName );
    }

}
