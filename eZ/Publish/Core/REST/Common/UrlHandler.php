<?php
/**
 * File containing the UrlHandler interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common;

/**
 * Interface for URL handlers
 */
interface UrlHandler
{
    /**
     * Parse URL and return the IDs contained in the URL
     *
     * @param string $type
     * @param string $url
     *
     * @return array
     */
    public function parse( $type, $url );

    /**
     * Generate a URL of the given type from the specified values
     *
     * @param string $type
     * @param array $values
     *
     * @return string
     */
    public function generate( $type, array $values = array() );
}
