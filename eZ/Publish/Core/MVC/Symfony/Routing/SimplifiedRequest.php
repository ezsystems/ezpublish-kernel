<?php
/**
 * File containing the SimplifiedRequest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Routing;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @property-read string $scheme The request scheme - http or https
 * @property-read string $host The host name
 * @property-read string $port The port the request is made on
 * @property-read string $pathinfo The path being requested relative to the executed script
 * @property-read array $queryParams Array of parameters extracted from the query string
 * @property-read array $languages List of languages acceptable by the client browser
 * @property-read array $headers Hash of request headers
 */
class SimplifiedRequest extends ValueObject
{
    /**
     * The request scheme (http or https).
     *
     * @var string
     */
    protected $scheme;

    /**
     * The host name.
     *
     * @var string
     */
    protected $host;

    /**
     * The port the request is made on.
     *
     * @var string
     */
    protected $port;

    /**
     * The path being requested relative to the executed script.
     * The path info always starts with a /.
     *
     * @var string
     */
    protected $pathinfo;

    /**
     * Array of parameters extracted from the query string.
     *
     * @var array
     */
    protected $queryParams;

    /**
     * List of languages acceptable by the client browser.
     * The languages are ordered in the user browser preferences.
     *
     * @var array
     */
    protected $languages;

    /**
     * Hash of request headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Constructs a SimplifiedRequest object from a standard URL (http://www.example.com/foo/bar?queryParam=value)
     *
     * @param string $url
     *
     * @internal
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest
     */
    public static function fromUrl( $url )
    {
        $elements = parse_url( $url );
        $elements['pathinfo'] = isset( $elements['path'] ) ? $elements['path'] : '';

        if ( isset( $elements['query'] ) )
        {
            parse_str( $elements['query'], $queryParams );
            $elements['queryParams'] = $queryParams;
        }

        // Remove unwanted keys returned by parse_url() so that we don't have them as properties.
        unset( $elements['path'], $elements['query'], $elements['user'], $elements['pass'], $elements['fragment'] );
        return new static( $elements );
    }
}
