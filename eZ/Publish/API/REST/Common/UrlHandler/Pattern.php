<?php
/**
 * File containing the Pattern UrlHandler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\UrlHandler;

use eZ\Publish\API\REST\Common\UrlHandler;
use eZ\Publish\API\REST\Common\Exceptions;

/**
 * Pattern based URL Handler
 */
class Pattern implements UrlHandler
{
    /**
     * Map of URL types to their URL patterns
     *
     * @var array
     */
    protected $map = array();

    /**
     * COnstruct from optional initial map
     *
     * @param array $map
     * @return void
     */
    public function __construct( array $map = array() )
    {
        foreach ( $map as $type => $pattern )
        {
            $this->addPattern( $type, $pattern );
        }
    }

    /**
     * Add a pattern for a type
     *
     * @param string $type
     * @param string $pattern
     * @return void
     */
    public function addPattern( $type, $pattern )
    {
        $this->map[$type] = $pattern;
    }

    /**
     * Parse URL and return the IDs contained in the URL
     *
     * @param string $type
     * @param string $url
     * @return array
     */
    public function parse( $type, $url )
    {
        if ( !isset( $this->map[$type] ) )
        {
            throw new Exceptions\InvalidArgumentException( "No URL for type '$type' available." );
        }

        $pattern = $this->compile( $this->map[$type] );

        if ( !preg_match( $pattern, $url, $match ) )
        {
            throw new Exceptions\InvalidArgumentException( "URL '$url' did not match $pattern." );
        }

        foreach ( $match as $key => $value )
        {
            if ( is_numeric( $key ) )
            {
                unset( $match[$key] );
            }
        }
        return $match;
    }

    /**
     * COmpiles a given pattern to a PCRE regular expression
     *
     * @param string $pattern
     * @return string
     */
    protected function compile( $pattern )
    {
        $pcre = '(^';

        do {
            switch ( true )
            {
                case preg_match( '(^[^{]+)', $pattern, $match ):
                    $pattern = substr( $pattern, strlen( $match[0] ) );
                    $pcre   .= preg_quote( $match[0] );
                    break;

                case preg_match( '(^\{([A-Za-z-_]+)\})', $pattern, $match ):
                    $pattern = substr( $pattern, strlen( $match[0] ) );
                    $pcre   .= "(?P<" . $match[1] . ">[^/]+)";
                    break;

                default:
                    throw new Exceptions\InvalidArgumentException( "Invalid pattern part: '$pattern'." );
            }
        } while ( $pattern );

        return $pcre . ')S';
    }

    /**
     * Generate a URL of the given type from the specified values
     *
     * @param string $type
     * @param array $values
     * @return string
     */
    public function generate( $type, array $values )
    {

    }
}

