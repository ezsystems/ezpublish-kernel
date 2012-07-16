<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Router class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess;

use eZ\Publish\MVC\SiteAccess,
    eZ\Publish\MVC\Routing\SimplifiedRequest;

class Router
{
    /**
     * Name of the default siteaccess
     *
     * @var string
     */
    protected $defaultSiteAccess;

    /**
     * The configuration for siteaccess matching.
     * Consists in an hash indexed by matcher type class.
     * Value is a hash where index is what to match against and value is the corresponding siteaccess name.
     *
     * Example:
     * <code>
     * array(
     *     // Using built-in URI matcher. Key is the prefix that matches the siteaccess, in the value
     *     "Map\\URI" => array(
     *         "ezdemo_site" => "ezdemo_site",
     *         "ezdemo_site_admin" => "ezdemo_site_admin",
     *     ),
     *     // Using built-in HOST matcher. Key is the hostname, value is the siteaccess name
     *     "Map\\Host" => array(
     *         "ezpublish.dev" => "ezdemo_site",
     *         "ezpublish.admin.dev" => "ezdemo_site_admin",
     *     ),
     *     // Using a custom matcher (class must begin with a '\', as a full qualifed class name).
     *     // The custom matcher must implement eZ\Publish\MVC\SiteAccess\Matcher interface.
     *     "\\My\\Custom\\Matcher" => array(
     *         "something_to_match_against" => "siteaccess_name"
     *     )
     * )
     * </code>
     * @var array
     */
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
     * Performs SiteAccess matching given the $request.
     *
     * @param \eZ\Publish\MVC\Routing\SimplifiedRequest $request
     *
     * @return string
     */
    public function match( SimplifiedRequest $request )
    {
        $siteaccess = new SiteAccess;

        // First check environment variable
        $siteaccessEnvName = getenv( 'EZPUBLISH_SITEACCESS' );
        if ( $siteaccessEnvName !== false )
        {
            // TODO: Check siteaccess validity and throw \RuntimeException if invalid
            $siteaccess->name = $siteaccessEnvName;
            $siteaccess->matchingType = 'env';
            return $siteaccess;
        }

        foreach ( $this->siteAccessesConfiguration as $matchingClass => $matchingConfiguration )
        {
            // If class begins with a '\' it means it's a FQ class name,
            // otherwise it is relative to this namespace.
            if ( $matchingClass[0] !== '\\' )
                $matchingClass = __NAMESPACE__ . "\\Matcher\\$matchingClass";

            $matcher = new $matchingClass( $matchingConfiguration );
            $matcher->setRequest( $request );

            if ( ( $siteaccessName = $matcher->match() ) !== false )
            {
                $siteaccess->name = $siteaccessName;
                $siteaccess->matchingType = $matcher->getName();
                $siteaccess->matcher = $matcher;
                return $siteaccess;
            }
        }

        $siteaccess->name = $this->defaultSiteAccess;
        $siteaccess->matchingType = 'default';
        return $siteaccess;
    }
}
