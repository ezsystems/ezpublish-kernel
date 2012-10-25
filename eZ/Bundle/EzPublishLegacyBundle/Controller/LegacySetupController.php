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
        $this->legacyKernelClosure = $kernelClosure;
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
        $result = $this->getLegacyKernel()->run();
        $result->getContent();
        $response->setContent( $result->getContent() );

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->container->get( 'request' );

        if ( $request->request->get( 'eZSetup_current_step' ) == 'Registration' )
        {
            $chosenSitePackage = $request->request->get( 'P_chosen_site_package-0' );
            $adminSiteaccess = $chosenSitePackage . '_admin';

            /** @var $configurationConverter \eZ\Bundle\EzPublishLegacyBundle\SetupWizard\ConfigurationConverter */
            $configurationConverter = $this->container->get( 'ezpublish_legacy.setup_wizard.configuration_converter' );

            $dumper = new Dumper();

            $settingsArray = $configurationConverter->fromLegacy( $chosenSitePackage, $adminSiteaccess );

            // add the import statement for the root YAML file
            $settingsArray['imports'] = array( array( 'resource' => 'ezpublish.yml' ) );

            $kernel = $this->container->get( 'kernel' );
            file_put_contents(
                $kernel->getRootdir() . '/config/ezpublish_' . $kernel->getEnvironment(). '.yml',
                $dumper->dump( $settingsArray, 5 )
            );

            /** @var $filesystem \Symfony\Component\Filesystem\Filesystem */
            $filesystem = $this->container->get( 'filesystem' );
            $cacheDir = $this->container->getParameter( 'kernel.cache_dir' );
            $oldCacheDirName = $cacheDir . '_old';
            $filesystem->rename( $cacheDir, $oldCacheDirName );
            $filesystem->remove( $oldCacheDirName );

        }

        return $response;
    }
}
