<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Router class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess;

class Router
{
    protected $defaultSiteAccess;

    protected $siteAccessesConfiguration;

    /**
     * Constructor.
     *
     * @param string $defaultSiteAccess
     * @param array $siteAccessesConfiguration
     */
    public function __construct( $defaultSiteAccess, array $siteAccessesConfiguration )
    {
        $this->defaultSiteAccess = $defaultSiteAccess;
        $this->siteAccessesConfiguration = $siteAccessesConfiguration;
    }

    /**
     * Performs SiteAccess matching given the $url.
     *
     * @param string $url
     *
     * @return string
     */
    public function match( $url )
    {
        if ( isset( $_SERVER["SITEACCESS"] ) )
            return $_SERVER["SITEACCESS"];

        $urlElements = parse_url( $url );

        foreach ( $this->siteAccessesConfiguration as $matchingClass => $matchingConfiguration )
        {
            // If class begins with a '\' it means it's a FQ class name,
            // otherwise it is relative to this namespace.
            if ( $matchingClass[0] !== '\\' )
                $matchingClass = __NAMESPACE__ . "\\Matcher\\$matchingClass";

            $matcher = new $matchingClass( $urlElements, $matchingConfiguration );

            if ( ( $siteaccess = $matcher->match() ) !== false )
                        return $siteaccess;
        }

        return $this->defaultSiteAccess;
    }
}