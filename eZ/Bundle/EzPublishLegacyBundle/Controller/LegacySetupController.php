<?php
/**
 * File containing the LegacySetupController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use eZ\Publish\Core\MVC\Legacy\Kernel\Loader;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\ConfigDumperInterface;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver;
use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use eZINI;
use eZCache;

class LegacySetupController extends ContainerAware
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
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel\Loader
     */
    protected $kernelFactory;

    /**
     * @param \Closure $kernelClosure
     * @param \eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver $legacyConfigResolver
     * @param \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger $persistenceCachePurger
     * @param \eZ\Publish\Core\MVC\Legacy\Kernel\Loader $kernelFactory
     */
    public function __construct(
        \Closure $kernelClosure,
        LegacyConfigResolver $legacyConfigResolver,
        PersistenceCachePurger $persistenceCachePurger,
        Loader $kernelFactory
    )
    {
        $this->legacyKernelClosure = $kernelClosure;
        $this->legacyConfigResolver = $legacyConfigResolver;
        $this->persistenceCachePurger = $persistenceCachePurger;
        $this->kernelFactory = $kernelFactory;
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
        $this->persistenceCachePurger->setEnabled( false );

        // we disable injection of settings to Legacy Kernel during setup
        $this->kernelFactory->setBuildEventsEnabled( false );

        /** @var $request \Symfony\Component\HttpFoundation\ParameterBag */
        $request = $this->container->get( 'request' )->request;

        // inject the extra ezpublish-community folders we want permissions checked for
        switch ( $request->get( 'eZSetup_current_step' ) )
        {
            case "Welcome":
            case "SystemCheck":
                $this->getLegacyKernel()->runCallback(
                    function ()
                    {
                        eZINI::injectSettings(
                            array(
                                "setup.ini" => array(
                                    // checked folders are relative to the ezpublish_legacy folder
                                    "directory_permissions" => array(
                                        "CheckList" => "../ezpublish/logs;../ezpublish/cache;../ezpublish/config;" .
                                        eZINI::instance( 'setup.ini' )->variable( 'directory_permissions', 'CheckList' )
                                    )
                                )
                            )
                        );
                    }
                );
        }

        $response = new Response();
        $response->setContent(
            $this->getLegacyKernel()->run()->getContent()
        );

        // After the latest step, we can re-use both POST data and written INI settings
        // to generate a local ezpublish_<env>.yml

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
            // If using kickstart.ini, legacy wizard will artificially create entries in $_POST
            // and in this case Symfony Request is not aware of them.
            // We then add them manually to the ParameterBag.
            if ( !$request->has( 'P_chosen_site_package-0' ) )
            {
                $request->add( $_POST );
            }
            $chosenSitePackage = $request->get( 'P_chosen_site_package-0' );

            // match mode (host, url or port)
            switch ( $request->get( 'P_site_extra_data_access_type-' . $chosenSitePackage ) )
            {
                case "hostname":
                case "port":
                    $adminSiteaccess = $chosenSitePackage . '_admin';
                    break;
                case "url":
                    $adminSiteaccess = $request->get( 'P_site_extra_data_admin_access_type_value-' . $chosenSitePackage );
            }

            /** @var $configurationDumper \eZ\Bundle\EzpublishLegacyBundle\SetupWizard\ConfigurationDumper */
            $configurationDumper = $this->container->get( 'ezpublish_legacy.setup_wizard.configuration_dumper' );
            $configurationDumper->addEnvironment( $this->container->get( 'kernel' )->getEnvironment() );
            $configurationDumper->dump(
                $this->container->get( 'ezpublish_legacy.setup_wizard.configuration_converter' )
                    ->fromLegacy( $chosenSitePackage, $adminSiteaccess ),
                ConfigDumperInterface::OPT_BACKUP_CONFIG
            );
        }

        return $response;
    }
}
