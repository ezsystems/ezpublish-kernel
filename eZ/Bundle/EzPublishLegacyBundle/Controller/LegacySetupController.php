<?php
/**
 * File containing the LegacySetupController class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface as Container,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Yaml\Dumper;

class LegacySetupController
{
    /**
     * The legacy kernel instance (eZ Publish 4)
     *
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @todo Maybe following dependencies should be mutualized in an abstract controller
     *       Injection can be done through "parent service" feature for DIC : http://symfony.com/doc/master/components/dependency_injection/parentservices.html
     * @param \Closure $kernelClosure
     */
    public function __construct( \Closure $kernelClosure )
    {
        $this->legacyKernelClosure = $kernelClosure();
    }

    public function setContainer( Container $container )
    {
        $this->container = $container;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;
        return $legacyKernelClosure();
    }

    public function init()
    {
        $response = new Response();

        /** @var \ezpKernelResult $result  */
        $result = $this->legacyKernelClosure->run();
        $result->getContent();
        $response->setContent( $result->getContent() );

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->container->get( 'request' );

        // eZPublish 5 post install
        if ( $request->request->get( 'eZSetup_current_step' ) == 'Registration' )
        {
            /** @var $legacyResolver \eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver*/
            $legacyResolver = $this->container->get( 'ezpublish_legacy.config.resolver' );

            $dumper = new Dumper();

            $settings = array();
            $settings['ezpublish'] = array();
            $settings['ezpublish']['siteaccess'] = array();
            $defaultSiteaccess = $legacyResolver->getParameter( 'SiteSettings.DefaultAccess' );
            $settings['ezpublish']['siteaccess']['default_siteaccess'] = $defaultSiteaccess;
            $siteList = $legacyResolver->getParameter( 'SiteSettings.SiteList' );
            $settings['ezpublish']['siteaccess']['list'] = $siteList;
            $settings['ezpublish']['siteaccess']['groups'] = array();
            $groupName = $defaultSiteaccess . '_group';
            $settings['ezpublish']['siteaccess']['groups'][$groupName] = $siteList;
            $settings['ezpublish']['siteaccess']['match'] = $this->resolveMatching( $legacyResolver );

            $databaseMapping = array(
                'ezmysqli' => 'mysql',
                'eZMySQLiDB' => 'mysql',
                'ezmysql' => 'mysql',
                'eZMySQLDB' => 'mysql',
            );
            $databaseType = $legacyResolver->getParameter( 'DatabaseSettings.DatabaseImplementation' );
            if ( isset( $databaseMapping[$databaseType] ) )
                $databaseType = $databaseMapping[$legacyResolver->getParameter( 'DatabaseSettings.DatabaseImplementation' )];

            $settings['ezpublish']['system'] = array();
            $settings['ezpublish']['system'][$groupName] = array();
            $settings['ezpublish']['system'][$groupName]['database'] = array(
                'type' => $databaseType,
                'user' => $legacyResolver->getParameter( 'DatabaseSettings.User', $defaultSiteaccess ),
                'password' => $legacyResolver->getParameter( 'DatabaseSettings.Password', $defaultSiteaccess ),
                'server' => $legacyResolver->getParameter( 'DatabaseSettings.Server', $defaultSiteaccess ),
                'database_name' => $legacyResolver->getParameter( 'DatabaseSettings.Database', 'site', $defaultSiteaccess ),
            );

            $yaml = $dumper->dump( $settings, 5 );
            file_put_contents( $this->container->get('kernel')->getRootdir() . '/config/ezpublish.yml', $yaml );
        }

        return $response;
    }

    protected function resolveMatching( $legacyResolver )
    {
        $siteaccessSettings = $legacyResolver->getGroup( 'SiteAccessSettings' );

        $matching = array();
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
                    $match = array( 'Map\Port' => $legacyResolver->getGroup( 'PortAccessSettings' ) );
                    break;
            }
            if ( $match !== false )
                $matching += $match;
        }
        return $match;
    }

    protected function resolveUriMatching( $siteaccessSettings )
    {
        switch( $siteaccessSettings['URIMatchType'] )
        {
            case 'disabled':
                return false;

            case 'map':
                return array( "Map\Uri" => $this->resolveMapMatch( $siteaccessSettings['URIMatchMapItems'] ) );

            case 'element':
                return array( "URIElement" => $siteaccessSettings['URIMatchElement'] );

            case 'text':
                return array( "URIText" => $this->resolveTextMatch( $siteaccessSettings, 'URIMatchSubtextPre', 'URIMatchSubtextPost' ) );

            case 'regexp':
                return array( "Regex\URI" => array( $siteaccessSettings['URIMatchRegexp'], $siteaccessSettings['URIMatchRegexpItem'] ) );
        }
    }

    protected function resolveHostMatching( $siteaccessSettings )
    {
        switch( $siteaccessSettings['HostMatchType'] )
        {
            case 'disabled':
                return false;

            case 'map':
                return array( "Map\Host" => $this->resolveMapMatch( $siteaccessSettings['HostMatchMapItems'] ) );

            case 'element':
                return array( "HostElement" => $siteaccessSettings['HostMatchElement'] );

            case 'text':
                return array( "HostText" => $this->resolveTextMatch( $siteaccessSettings, 'HostMatchSubtextPre', 'HostMatchSubtextPost' ) );

            case 'regexp':
                return array( "Regex\Host" => array( $siteaccessSettings['HostMatchRegexp'], $siteaccessSettings['HostMatchRegexpItem'] ) );
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