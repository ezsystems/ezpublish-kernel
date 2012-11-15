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
    eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel,
    eZINI,
    eZSiteAccess;

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

    /**
     * @var array
     */
    protected $supportedPackages;

    public function __construct( LegacyConfigResolver $legacyResolver, \Closure $legacyKernel, array $supportedPackages )
    {
        $this->legacyResolver = $legacyResolver;
        $this->legacyKernel = $legacyKernel();
        $this->supportedPackages = array_fill_keys( $supportedPackages, true );
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
        $settings = array();
        $settings['ezpublish'] = array();
        $settings['ezpublish']['siteaccess'] = array();
        $defaultSiteaccess = $this->getParameter( 'SiteSettings', 'DefaultAccess' );
        $settings['ezpublish']['siteaccess']['default_siteaccess'] = $defaultSiteaccess;
        $siteList = $this->getParameter( 'SiteAccessSettings', 'AvailableSiteAccessList' );

        if ( !is_array( $siteList ) || empty( $siteList ) )
            throw new InvalidArgumentException( 'siteList', 'can not be empty' );

        if ( !in_array( $adminSiteaccess, $siteList ) )
            throw new InvalidArgumentException( "adminSiteaccess", "Siteaccess $adminSiteaccess wasn't found in SiteAccessSettings.AvailableSiteAccessList" );

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

        $databaseSettings = $this->getGroup( 'DatabaseSettings', 'site.ini', $defaultSiteaccess );

        $databaseType = $databaseSettings['DatabaseImplementation'];
        if ( isset( $databaseMapping[$databaseType] ) )
            $databaseType = $databaseMapping[$databaseType];

        $settings['ezpublish']['system'] = array();
        $settings['ezpublish']['system'][$groupName] = array();
        $databasePassword = $databaseSettings['Password'] != '' ? $databaseSettings['Password'] : null;
        $settings['ezpublish']['system'][$groupName]['database'] = array(
            'type' => $this->mapDatabaseType( $databaseType ),
            'user' => $databaseSettings['User'],
            'password' => $databasePassword,
            'server' => $databaseSettings['Server'],
            'database_name' => $databaseSettings['Database'],
        );
        $settings['ezpublish']['system'][$defaultSiteaccess] = array();
        $settings['ezpublish']['system'][$adminSiteaccess] = array();

        // If package is not supported, all siteaccesses will have individually legacy_mode to true, forcing legacy fallback
        if ( !isset( $this->supportedPackages[$sitePackage] ) )
        {
            foreach ( $siteList as $siteaccess )
            {
                $settings['ezpublish']['system'][$siteaccess] = array( 'legacy_mode' => true );
            }
        }
        else
        {
            $settings['ezpublish']['system'][$adminSiteaccess] += array( 'legacy_mode' => true );
        }

        // FileSettings
        $settings['ezpublish']['system'][$groupName]['var_dir'] =
            $this->getParameter( 'FileSettings', 'VarDir', 'site.ini', $defaultSiteaccess );

        // we don't map the default FileSettings.StorageDir value
        $storageDir = $this->getParameter( 'FileSettings', 'StorageDir', 'site.ini', $defaultSiteaccess );
        if ( $storageDir !== 'storage' )
            $settings['ezpublish']['system'][$groupName]['storage_dir'] = $storageDir;


        // ImageMagick settings
        $imageMagickEnabled = $this->getParameter( 'ImageMagick', 'IsEnabled', 'image.ini', $defaultSiteaccess );
        if ( $imageMagickEnabled == 'true' )
        {
            $settings['ezpublish']['imagemagick']['enabled'] = true;
            $imageMagickExecutablePath = $this->getParameter( 'ImageMagick', 'ExecutablePath', 'image.ini', $defaultSiteaccess );
            $imageMagickExecutable = $this->getParameter( 'ImageMagick', 'Executable', 'image.ini', $defaultSiteaccess );
            $settings['ezpublish']['imagemagick']['path'] = rtrim( $imageMagickExecutablePath, '/\\' ) . '/' . $imageMagickExecutable;
        }
        else
        {
            $settings['ezpublish']['imagemagick']['enabled'] = false;
        }

        // image variations settings
        $settings['ezpublish']['system'][$defaultSiteaccess]['image_variations'] = array();
        $imageAliasesList = $this->getGroup( 'AliasSettings', 'image.ini', $defaultSiteaccess );
        foreach( $imageAliasesList['AliasList'] as $imageAliasIdentifier )
        {
            $variationSettings = array( 'reference' => null, 'filters' => array() );
            $aliasSettings = $this->getGroup( $imageAliasIdentifier, 'image.ini', $defaultSiteaccess );
            if ( isset( $aliasSettings['Reference'] ) && $aliasSettings['Reference'] != '' )
            {
                $variationSettings['reference'] = $aliasSettings['Reference'];
            }
            if ( isset( $aliasSettings['Filters'] ) && is_array( $aliasSettings['Filters'] ) )
            {
                // parse filters. Format: filtername=param1;param2...paramN
                foreach( $aliasSettings['Filters'] as $filterString )
                {
                    $filteringSettings = array();

                    if ( strstr( $filterString, '=' ) !== false )
                    {
                        list( $filteringSettings['name'], $filterParams) = explode( '=', $filterString );
                        $filterParams = explode( ';', $filterParams );

                        // make sure integers are actually integers, not strings
                        array_walk( $filterParams, function( &$value ) {
                            if ( preg_match( '/^[0-9]+$/', $value ) )
                                $value = (int)$value;
                        } );

                        $filteringSettings['params'] = $filterParams;
                    }
                    else
                    {
                        $filteringSettings['name'] = $filterString;
                    }

                    $variationSettings['filters'][] = $filteringSettings;
                }
            }

            $settings['ezpublish']['system'][$defaultSiteaccess]['image_variations'][$imageAliasIdentifier] = $variationSettings;
        }

        // Explicitely set Http cache purge type to "local"
        $settings['ezpublish']['http_cache']['purge_type'] = 'local';

        return $settings;
    }

    protected function mapDatabaseType( $databaseType )
    {
        $map = array(
            'ezpostgresql' => 'pgsql',
            'postgresql' => 'pgsql'
        );

        return isset( $map[$databaseType] ) ? $map[$databaseType] : $databaseType;
    }

    /**
     * Returns the contents of the legacy group $groupName. If $file and
     * $siteaccess are null, the global value is fetched with the legacy resolver.
     *
     * @param string $groupName
     * @param string|null $namespace
     * @param string|null $siteaccess
     *
     * @return array
     */
    public function getGroup( $groupName, $file = null, $siteaccess = null )
    {
        if ( $file === null && $siteaccess === null )
        {
            // in this case we want the "global" value, no need to use the
            // legacy kernel, the legacy resolver is enough
            return $this->legacyResolver->getGroup( $groupName );
        }
        return $this->legacyKernel->runCallback(
            function () use ( $file, $groupName, $siteaccess )
            {
                // TODO: do reset injected settings everytime
                // and make sure to restore the previous injected settings
                eZINI::injectSettings( array() );
                return eZSiteAccess::getIni( $siteaccess, $file )->group( $groupName );
            }
        );
    }

    /**
     * Returns the value of the legacy parameter $parameterName in $groupName.
     * If $file and $siteaccess are null, the global value is fetched with the
     * legacy resolver.
     *
     * @param string $groupName
     * @param string $parameterName
     * @param string|null $file
     * @param string|null $siteaccess
     *
     * @return array
     */
    public function getParameter( $groupName, $parameterName, $file = null, $siteaccess = null )
    {
        if ( $file === null && $siteaccess === null )
        {
            // in this case we want the "global" value, no need to use the
            // legacy kernel, the legacy resolver is enough
            return $this->legacyResolver->getParameter( "$groupName.$parameterName" );
        }
        return $this->legacyKernel->runCallback(
            function () use ( $file, $groupName, $parameterName, $siteaccess )
            {
                // TODO: do reset injected settings everytime
                // and make sure to restore the previous injected settings
                eZINI::injectSettings( array() );
                return eZSiteAccess::getIni( $siteaccess, $file )
                    ->variable( $groupName, $parameterName );
            }
        );
    }

    protected function resolveMatching()
    {
        $siteaccessSettings = $this->getGroup( 'SiteAccessSettings' );

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
                    $match = array( 'Map\Port' => $this->getGroup( 'PortAccessSettings' ) );
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
