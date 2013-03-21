<?php
/**
 * File containing the LegacySetupController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\ConfigDumperInterface;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver;
use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use eZINI;
use eZCache;

class LegacySetupController
{
    /**
     * The legacy kernel instance (eZ Publish 4)
     *
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * The legacy config resolver
     *
     * @var \eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver
     */
    private $legacyConfigResolver;

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger
     */
    private $persistenceCachePurger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @todo Maybe following dependencies should be mutualized in an abstract controller
     *       Injection can be done through "parent service" feature for DIC : http://symfony.com/doc/master/components/dependency_injection/parentservices.html
     *
     * @param \Closure $kernelClosure
     * @param \eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver $legacyConfigResolver
     * @param \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger $persistenceCachePurger
     */
    public function __construct( \Closure $kernelClosure, LegacyConfigResolver $legacyConfigResolver, PersistenceCachePurger $persistenceCachePurger )
    {
        $this->legacyKernelClosure = $kernelClosure;
        $this->legacyConfigResolver = $legacyConfigResolver;
        $this->persistenceCachePurger = $persistenceCachePurger;
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
        // Ensure that persistence cache purger is disabled as legacy cache will be cleared by legacy setup wizard while
        // everything is not ready yet to clear SPI cache (no connection to repository yet).
        $this->persistenceCachePurger->setIsEnabled( false );

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->container->get( 'request' );
        $currentStep = $request->request->get( 'eZSetup_current_step' );

        $response = new Response();

        // inject the extra ezpublish-community folders we want permissions checked for
        if ( $currentStep == 'Welcome' || $currentStep == 'SystemCheck' )
        {
            $this->getLegacyKernel()->runCallback(
                function ()
                {
                    $directoriesCheckList = eZINI::instance( 'setup.ini' )->variable( 'directory_permissions', 'CheckList' );
                    $injectedSettings = array();
                    // checked folders are relative to the ezpublish_legacy folder
                    $injectedSettings['setup.ini']['directory_permissions']['CheckList'] =
                        "../ezpublish/logs;../ezpublish/cache;../ezpublish/config;" . $directoriesCheckList;
                    eZINI::injectSettings( $injectedSettings );
                }
            );
        }

        /** @var \ezpKernelResult $result */
        $result = $this->getLegacyKernel()->run();
        $result->getContent();
        $response->setContent( $result->getContent() );

        // After the registration step, we can re-use both POST data and written INI settings
        // to generate a local ezpublish_<env>.yml
        if ( $currentStep == 'Registration' )
        {
            // Clear INI cache since setup has written new files
            $this->getLegacyKernel()->runCallback(
                function ()
                {
                    eZINI::injectSettings( array() );
                    eZCache::clearByTag( 'ini' );
                    eZINI::resetAllInstances();
                }
            );

            // Check that eZ Publish Legacy was actually installed, since one step can run several steps
            if ( $this->legacyConfigResolver->getParameter( 'SiteAccessSettings.CheckValidity' ) == 'false' )
            {
                $chosenSitePackage = $request->request->get( 'P_chosen_site_package-0' );

                // match mode (host, url or port)
                $accessType = $request->request->get( 'P_site_extra_data_access_type-' . $chosenSitePackage );
                if ( $accessType == 'hostname' || $accessType == 'port' )
                {
                    $adminSiteaccess = $chosenSitePackage . '_admin';
                }
                else if ( $accessType === 'url' )
                {
                    $adminSiteaccess = $request->request->get( 'P_site_extra_data_admin_access_type_value-' . $chosenSitePackage );
                }

                /** @var $configurationConverter \eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter */
                $configurationConverter = $this->container->get( 'ezpublish_legacy.setup_wizard.configuration_converter' );
                /** @var $configurationDumper \eZ\Bundle\EzpublishLegacyBundle\SetupWizard\ConfigurationDumper */
                $configurationDumper = $this->container->get( 'ezpublish_legacy.setup_wizard.configuration_dumper' );
                $configurationDumper->addEnvironment( $this->container->get( 'kernel' )->getEnvironment() );
                $configurationDumper->dump(
                    $configurationConverter->fromLegacy( $chosenSitePackage, $adminSiteaccess ),
                    ConfigDumperInterface::OPT_BACKUP_CONFIG
                );
            }
        }

        return $response;
    }
}
