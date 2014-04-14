<?php
/**
 * File containing the LegacyResponse class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LegacyResponse
 *
 * An extend of the Symfony Response class with an extra module result attribute.
 * It can be useful if you need to access module result information in an event listener.
 *
 */
class LegacyResponse extends Response
{
    /**
     * Module result sent by the legacy stack.
     *
     * @var array
     */
    protected $moduleResult = null;

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

    /*
     * Sets the module result in the response.
     *
     * @param array $moduleResult
     */
    public function setModuleResult( $moduleResult )
    {
        $this->moduleResult = $moduleResult;
    }

    /**
     * Gets the module result if it exists.
     *
     * @return array result or null if it doesn't exist.
     */
    public function getModuleResult()
    {
        return $this->moduleResult;
    }

}
