<?php
/**
 * File containing the LocationAwareStore class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use Symfony\Component\HttpFoundation\Response;

/**
 * Store implements all the logic for storing cache metadata (Request and Response headers).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LocationAwareStore extends Store
{
    /**
     * Injects eZ Publish specific information in the content digest if needed.
     * X-Location-Id response header is set in the ViewController
     *
     * @see \eZ\Publish\Core\MVC\Symfony\Controller\Content\ViewController::viewLocation()
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return string
     */
    protected function generateContentDigest( Response $response )
    {
        $digest = parent::generateContentDigest( $response );
        if ( !$response->headers->has( 'X-Location-Id' ) )
        {
            return $digest;
        }

        return "ezlocation/{$response->headers->get( 'X-Location-Id' )}/$digest";
    }

    /**
     * Returns the right path where cache is being stored.
     * Will detect if $key is eZ Publish specific.
     *
     * @param string $key
     * @return string
     */
    public function getPath( $key )
    {
        if ( strpos( $key, 'ezlocation' ) === false )
            return parent::getPath( $key );

        $prefix = '';
        if ( ( $pos = strrpos( $key, '/' ) ) !== false )
        {
            $prefix = substr( $key, 0, $pos ) . DIRECTORY_SEPARATOR;
            $key = substr( $key, $pos + 1 );
        }

        return $this->root . DIRECTORY_SEPARATOR . $prefix .
           substr( $key, 0, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 2, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 4, 2 ) . DIRECTORY_SEPARATOR .
           substr( $key, 6 )
        ;
    }
}
