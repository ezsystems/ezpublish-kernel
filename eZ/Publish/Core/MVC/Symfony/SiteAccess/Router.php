<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Router class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess,
    eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest,
    eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException;

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
     *     // The custom matcher must implement eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher interface.
     *     "\\My\\Custom\\Matcher" => array(
     *         "something_to_match_against" => "siteaccess_name"
     *     )
     * )
     * </code>
     * @var array
     */
    protected $siteAccessesConfiguration;

    /**
     * List of configured siteaccesses.
     * Siteaccess name is the key, "true" is the value.
     *
     * @var array
     */
    protected $siteAccessList;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    protected $siteAccess;

    protected $siteAccessClass;

    /**
     * Constructor.
     *
     * @param string $defaultSiteAccess
     * @param array $siteAccessesConfiguration
     * @param array $siteAccessList
     * @param null $siteAccessClass
     */
    public function __construct( $defaultSiteAccess, array $siteAccessesConfiguration, array $siteAccessList, $siteAccessClass = null )
    {
        $this->defaultSiteAccess = $defaultSiteAccess;
        $this->siteAccessesConfiguration = $siteAccessesConfiguration;
        $this->siteAccessList = array_fill_keys( $siteAccessList, true );
        $this->siteAccessClass = $siteAccessClass;
    }

    /**
     * Performs SiteAccess matching given the $request.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     *
     * @throws \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function match( SimplifiedRequest $request )
    {
        if ( isset( $this->siteAccess ) )
            return $this->siteAccess;

        $siteAccessClass = $this->siteAccessClass ?: 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess';
        $this->siteAccess = new $siteAccessClass();

        // Request header always have precedence
        if ( isset( $request->headers['X-Siteaccess'] ) )
        {
            $siteaccessName = $request->headers['X-Siteaccess'];
            if ( !isset( $this->siteAccessList[$siteaccessName] ) )
            {
                unset( $this->siteAccess );
                throw new InvalidSiteAccessException( $siteaccessName, array_keys( $this->siteAccessList ), 'X-Siteaccess request header' );
            }

            $this->siteAccess->name = $request->headers['X-Siteaccess'];
            $this->siteAccess->matchingType = 'header';
            return $this->siteAccess;
        }

        // Then check environment variable
        $siteaccessEnvName = getenv( 'EZPUBLISH_SITEACCESS' );
        if ( $siteaccessEnvName !== false )
        {
            if ( !isset( $this->siteAccessList[$siteaccessEnvName] ) )
            {
                unset( $this->siteAccess );
                throw new InvalidSiteAccessException( $siteaccessEnvName, array_keys( $this->siteAccessList ), 'EZPUBLISH_SITEACCESS Environment variable' );
            }

            $this->siteAccess->name = $siteaccessEnvName;
            $this->siteAccess->matchingType = 'env';
            return $this->siteAccess;
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
                if ( isset( $this->siteAccessList[$siteaccessName] ) )
                {
                    $this->siteAccess->name = $siteaccessName;
                    $this->siteAccess->matchingType = $matcher->getName();
                    $this->siteAccess->matcher = $matcher;
                    return $this->siteAccess;
                }
            }
        }

        $this->siteAccess->name = $this->defaultSiteAccess;
        $this->siteAccess->matchingType = 'default';
        return $this->siteAccess;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess|null
     */
    public function getSiteAccess()
    {
        return $this->siteAccess;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     * @access private Only for unit tests use
     */
    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->siteAccess = $siteAccess;
    }
}
