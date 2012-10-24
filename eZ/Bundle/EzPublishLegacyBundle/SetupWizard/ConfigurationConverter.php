<?php
/**
 * File containing the ConfigurationConverter class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\SetupWizard;

use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;

/**
 * Handles conversionlegacy eZ Publish 4 parameters from a set of settings to a configuration array
 * suitable for yml dumping
 */
class ConfigurationConverter
{
    /**
     * @var eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver
     */
    protected $legacyResolver;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected $legacyKernel;

    public function __construct( LegacyConfigResolver $legacyResolver, \Closure $legacyKernel )
    {
        $this->legacyResolver = $legacyResolver;
        $this->legacyKernel = $legacyKernel();
    }

    /**
     * Converts from legacy settings to an array dumpable to ezpublish.yml
     * @param string $sitePackage Name of the chosen install package
     * @param string $adminSiteaccess Name of the admin siteaccess
     * @return array
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function fromLegacy( $sitePackage, $adminSiteaccess )
    {
        $this->legacyKernel->runCallback(
            function()
            {
               \eZINI::injectSettings( array() );
            }
        );

        $settings = array();
        $settings['ezpublish'] = array();
        $settings['ezpublish']['siteaccess'] = array();
        $defaultSiteaccess = $this->legacyResolver->getParameter( 'SiteSettings.DefaultAccess' );
        $settings['ezpublish']['siteaccess']['default_siteaccess'] = $defaultSiteaccess;
        $siteList = $this->legacyResolver->getParameter( 'SiteSettings.SiteList' );

        if ( !is_array( $siteList ) || empty( $siteList ) )
            throw new InvalidArgumentException( 'siteList', 'can not be empty' );

        if ( !in_array( $adminSiteaccess, $siteList ) )
            throw new InvalidArgumentException( "adminSiteaccess", "Siteacces $adminSiteaccess wasn't found in SiteSettings.SiteList" );

        $settings['ezpublish']['siteaccess']['list'] = $siteList;
        $settings['ezpublish']['siteaccess']['groups'] = array();
        $groupName = $sitePackage . '_group';
        $settings['ezpublish']['siteaccess']['groups'][$groupName] = $siteList;
        $settings['ezpublish']['siteaccess']['match'] = $this->resolveMatching();

        $databaseMapping = array(
            'ezmysqli' => 'mysql',
            'eZMySQLiDB' => 'mysql',
            'ezmysql' => 'mysql',
            'eZMySQLDB' => 'mysql',
        );

        $databaseSettings = $this->getGroupWithFallback( 'DatabaseSettings', 'site', $defaultSiteaccess );

        $databaseType = $databaseSettings['DatabaseImplementation'];
        if ( isset( $databaseMapping[$databaseType] ) )
            $databaseType = $databaseMapping[$databaseType];

        $settings['ezpublish']['system'] = array();
        $settings['ezpublish']['system'][$groupName] = array();
        $databasePassword = $databaseSettings['Password'] != '' ? $databaseSettings['Password'] : null;
        $settings['ezpublish']['system'][$groupName]['database'] = array(
            'type' => $databaseType,
            'user' => $databaseSettings['User'],
            'password' => $databasePassword,
            'server' => $databaseSettings['Server'],
            'database_name' => $databaseSettings['Database'],
        );
        $settings['ezpublish']['system'][$adminSiteaccess] = array( 'url_alias_router' => false );

        $settings['ezpublish']['system'][$groupName]['var_dir'] =
            $this->getParameterWithFallback( 'FileSettings.VarDir', 'site', $defaultSiteaccess );

        return $settings;
    }

    /**
     * Returns the contents of the legacy group $groupName, either in $defaultSiteaccess or,
     * if not found, in the global settings
     *
     * @param $groupName
     * @param $namespace
     * @param $siteaccess
     *
     * @internal param $defaultSiteaccess
     * @return array
     *
     * @throws \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException
     */
    public function getGroupWithFallback( $groupName, $namespace, $siteaccess )
    {
        try
        {
            return $this->legacyResolver->getGroup( $groupName, $namespace, $siteaccess );
        }
        // fallback to global override
        catch ( \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException $e )
        {
            return $this->legacyResolver->getGroup( $groupName, $namespace );
        }
    }

    /**
     * Returns the value of the legacy parameter $parameterName, either in $defaultSiteaccess or,
     * if not found, in the global settings
     *
     * @param $parameterName
     * @param $namespace
     * @param $siteaccess
     *
     * @internal param $groupName
     * @internal param $defaultSiteaccess
     * @return array
     *
     */
    public function getParameterWithFallback( $parameterName, $namespace, $siteaccess )
    {
        try
        {
            return $this->legacyResolver->getParameter( $parameterName, $namespace, $siteaccess );
        }
            // fallback to global override
        catch ( \eZ\Publish\Core\MVC\Exception\ParameterNotFoundException $e )
        {
            return $this->legacyResolver->getParameter( $parameterName, $namespace );
        }
    }

    protected function resolveMatching()
    {
        $siteaccessSettings = $this->legacyResolver->getGroup( 'SiteAccessSettings' );

        $matching = array(); $match = false;
        foreach( explode( ';', $siteaccessSettings['MatchOrder'] ) as $matchMethod )
        {
            switch( $matchMethod )
            {
                case 'uri':
                    $match = $this->resolveURIMatching( $siteaccessSettings );
                    break;
                case 'host':
                    $match = $this->resolveHostMatching( $siteaccessSettings );
                    break;
                case 'host_uri':
                    // @todo Not implemented yet
                    $match = false;
                    break;
                case 'port':
                    $match = array( 'Map\Port' => $this->legacyResolver->getGroup( 'PortAccessSettings' ) );
                    break;
            }
            if ( $match !== false )
            {
                $matching = $match + $matching;
            }

        }
        return $matching;
    }

    protected function resolveUriMatching( $siteaccessSettings )
    {
        switch( $siteaccessSettings['URIMatchType'] )
        {
            case 'disabled':
                return false;

            case 'map':
                return array( "Map\\Uri" => $this->resolveMapMatch( $siteaccessSettings['URIMatchMapItems'] ) );

            case 'element':
                return array( "URIElement" => $siteaccessSettings['URIMatchElement'] );

            case 'text':
                return array( "URIText" => $this->resolveTextMatch( $siteaccessSettings, 'URIMatchSubtextPre', 'URIMatchSubtextPost' ) );

            case 'regexp':
                return array( "Regex\\URI" => array( $siteaccessSettings['URIMatchRegexp'], $siteaccessSettings['URIMatchRegexpItem'] ) );
        }
    }

    /**
     * Parses Legacy HostMatching settings to a matching array
     * @param $siteaccessSettings
     *
     * @return array|bool
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    protected function resolveHostMatching( $siteaccessSettings )
    {
        switch( $siteaccessSettings['HostMatchType'] )
        {
            case 'disabled':
                return false;

            case 'map':
                return array( "Map\\Host" => $this->resolveMapMatch( $siteaccessSettings['HostMatchMapItems'] ) );

            case 'element':
                return array( "HostElement" => $siteaccessSettings['HostMatchElement'] );

            case 'text':
                return array( "HostText" => $this->resolveTextMatch( $siteaccessSettings, 'HostMatchSubtextPre', 'HostMatchSubtextPost' ) );

            case 'regexp':
                return array( "Regex\\Host" => array( $siteaccessSettings['HostMatchRegexp'], $siteaccessSettings['HostMatchRegexpItem'] ) );

            default:
                throw new InvalidArgumentException( "HostMatchType", "Invalid value for legacy setting site.ini '{$siteaccessSettings['HostMatchType']}'" );
        }
    }

    protected function resolveTextMatch( $siteaccessSettings, $prefixKey, $suffixKey )
    {
        $settings = array();
        if ( isset( $siteaccessSettings[$prefixKey] ) )
            $settings['prefix'] = $siteaccessSettings[$prefixKey];
        if ( isset( $siteaccessSettings[$suffixKey] ) )
            $settings['suffix'] = $siteaccessSettings[$suffixKey];

        return $settings;
    }

    protected function resolveMapMatch( $mapArray )
    {
        $map = array();
        foreach ( $mapArray as $mapItem )
        {
            $elements = explode( ';', $mapItem );
            $map[$elements[0]] = count( $elements ) > 2 ? array_slice( $elements, 1 ) : $elements[1];
        }

        return $map;
    }
}
