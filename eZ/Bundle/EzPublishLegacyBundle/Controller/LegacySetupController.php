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
//        $result = $this->legacyKernelClosure->run();
//        $result->getContent();
//        $response->setContent( $result->getContent() );

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->container->get( 'request' );

        /**
         * @todo remove me
         */
        $request->request->replace( $this->getSetupPostData() );

        // eZPublish 5 post install
        if ( $request->request->get( 'eZSetup_current_step' ) == 'CreateSites' )
        {
            // $response->setContent( print_r( $request->request->all(), true ) );

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
            $settings['ezpublish']['siteaccess']['groups'][$defaultSiteaccess . '_group'] = $siteList;
            $settings['ezpublish']['siteacces']['match'] = $this->resolveMatching( $legacyResolver );

            $settings['ezpublish']['system'] = array();

        }

        return $response;
    }

    protected function resolveMatching( $legacyResolver )
    {
        $siteaccessSettings = $legacyResolver->getGroup( 'SiteAccessSettings' ):

        $matching = array();
        foreach( explode( ';', $siteaccessSettings['MatchOrder'] ) as $matchMethod )
        {
            switch( $matchMethod )
            {
                case 'uri':
                    $match = $this->resolveURIMatching( $siteaccessSettings );
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

    /**
     * @todo remove me
     */
    private function getSetupPostData()
    {
        return array(
            "P_admin-email" => "bd@ez.no",
            "P_admin-first_name" => "Administrator",
            "P_admin-last_name" => "User",
            "P_admin-password" => "publish",
            "P_allow_url_fopen-result" => "1",
            "P_chosen_site_package-0" => "ezdemo_site",
            "P_database_extensions-checked" => array( 'mysqli', 'mysql', 'pgsql' ),
            "P_database_extensions-found" => array( 'mysqli', 'mysqli' ),
            "P_database_extensions-result" => "1",
            "P_database_info-database" => "",
            "P_database_info-driver" => "ezmysqli",
            "P_database_info-has_demo_data" => "1",
            "P_database_info-name" => "",
            "P_database_info-password" => "",
            "P_database_info-port" => "",
            "P_database_info-required_version" => "4.1.1",
            "P_database_info-server" => "localhost",
            "P_database_info-socket" => "",
            "P_database_info-supports_unicode" => "1",
            "P_database_info-type" => "mysqli",
            "P_database_info-use_unicode" => "1",
            "P_database_info-user" => "root",
            "P_database_info-version" => "5.5.24-0ubuntu0.12.04.1",
            "P_database_info_available-0" => "ezpublish",
            "P_database_info_available-1" => "ezpublish_ezflow",
            "P_database_info_available-2" => "ezpublish_unittests",
            "P_database_info_available-3" => "jira",
            "P_database_info_available-4" => "performance_schema",
            "P_database_info_available-5" => "test",
            "P_database_info_available-6" => "wit",
            "P_directory_permissions-result" => "1",
            "P_dom_extension-checked" => array( 'dom' ),
            "P_dom_extension-found" => array( 'dom' ),
            "P_dom_extension-result" => "1",
            "P_email_info-result" => "",
            "P_email_info-type" => "1",
            "P_execution_time-result" => "1",
            "P_ezcversion-required" => "2008.2",
            "P_ezcversion-result" => "1",
            "P_file_upload-result" => "1",
            "P_iconv_extension-checked" => array( 'iconv' ),
            "P_iconv_extension-found" => array( 'iconv' ),
            "P_iconv_extension-result" => "1",
            "P_imagegd_extension-result" => "",
            "P_imagemagick_program-extra_path" => "",
            "P_imagemagick_program-path" => "/usr/bin",
            "P_imagemagick_program-program" => "convert",
            "P_imagemagick_program-result" => "1",
            "P_magic_quotes_runtime-result" => "1",
            "P_mbstring_extension-result" => "1",
            "P_memory_limit-result" => "1",
            "P_optional_tests_run-allow_url_fopen" => "1",
            "P_optional_tests_run-database_extensions" => "1",
            "P_optional_tests_run-directory_permissions" => "1",
            "P_optional_tests_run-dom_extension" => "1",
            "P_optional_tests_run-execution_time" => "1",
            "P_optional_tests_run-ezcversion" => "1",
            "P_optional_tests_run-file_upload" => "1",
            "P_optional_tests_run-iconv_extension" => "1",
            "P_optional_tests_run-image_conversion" => "1",
            "P_optional_tests_run-magic_quotes_runtime" => "1",
            "P_optional_tests_run-mbstring_extension" => "1",
            "P_optional_tests_run-memory_limit" => "1",
            "P_optional_tests_run-php_session" => "1",
            "P_optional_tests_run-phpversion" => "1",
            "P_optional_tests_run-safe_mode" => "1",
            "P_optional_tests_run-timezone" => "1",
            "P_optional_tests_run-zlib_extension" => "1",
            "P_php_session-checked" => array( 'session' ),
            "P_php_session-found" => array( 'session' ),
            "P_php_session-result" => "1",
            "P_phpversion-found" => "5.3.10-1ubuntu3.4",
            "P_phpversion-required" => "5.3.3",
            "P_phpversion-result" => "1",
            "P_regional_info-enable_unicode" => "1",
            "P_regional_info-language_type" => "1",
            "P_regional_info-languages" => array( 'fre-FR', 'eng-GB' ),
            "P_regional_info-primary_language" => "eng-GB",
            "P_regional_info-site_charset" => "utf-8",
            "P_setup_wizard-language" => "fre-FR",
            "P_site_extra_data_access_type-ezdemo_site" => "url",
            "P_site_extra_data_access_type_value-ezdemo_site" => "ezdemo_site",
            "P_site_extra_data_admin_access_type_value-ezdemo_site" => "ezdemo_site_admin",
            "P_site_extra_data_database-ezdemo_site" => "ezpublish5_legacy",
            "P_site_extra_data_title-ezdemo_site" => "eZ Publish Demo Site",
            "P_site_extra_data_url-ezdemo_site" => "http://vm.ezpublish5",
            "P_tests_run-allow_url_fopen" => "1",
            "P_tests_run-database_extensions" => "1",
            "P_tests_run-directory_permissions" => "1",
            "P_tests_run-dom_extension" => "1",
            "P_tests_run-execution_time" => "1",
            "P_tests_run-ezcversion" => "1",
            "P_tests_run-file_upload" => "1",
            "P_tests_run-iconv_extension" => "1",
            "P_tests_run-image_conversion" => "1",
            "P_tests_run-magic_quotes_runtime" => "1",
            "P_tests_run-mbstring_extension" => "1",
            "P_tests_run-memory_limit" => "1",
            "P_tests_run-php_session" => "1",
            "P_tests_run-phpversion" => "1",
            "P_tests_run-safe_mode" => "1",
            "P_tests_run-timezone" => "1",
            "P_tests_run-zlib_extension" => "1",
            "P_use_kickstart-database_choice" => "1",
            "P_use_kickstart-database_init" => "1",
            "P_use_kickstart-email_settings" => "1",
            "P_use_kickstart-language_options" => "1",
            "P_use_kickstart-package_language_options" => "1",
            "P_use_kickstart-security" => "1",
            "P_use_kickstart-site_access" => "1",
            "P_use_kickstart-site_admin" => "1",
            "P_use_kickstart-site_details" => "1",
            "P_use_kickstart-site_types" => "1",
            "P_use_kickstart-system_check" => "1",
            "P_use_kickstart-system_finetune" => "1",
            "P_use_kickstart-welcome" => "1",
            "P_zlib_extension-checked" => array( 'zlib' ),
            "P_zlib_extension-found" => array( 'zlip' ),
            "P_zlib_extension-result" => "1",
            "eZSetupRegistrationData" => array( 'first_name' => 'Bertrand', 'last_name' => 'Dunogier', 'email' => 'bd@ez.no'),
            "eZSetupSendRegistration" => "checked",
            "eZSetup_current_step" => "CreateSites",
            "eZSetup_next_button" => "Next >"
        );
    }
}

