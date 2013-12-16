<?php
/**
 * File containing the LegacyKernelController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;
use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager;
use eZ\Publish\Core\MVC\Legacy\Kernel\URIHelper;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Controller embedding legacy kernel.
 */
class LegacyKernelController
{
    /**
     * The legacy kernel instance (eZ Publish 4)
     *
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    private $kernel;

    /**
     * Template declaration to wrap legacy responses in a Twig pagelayout (optional)
     * Either a template declaration string or null/false to use legacy pagelayout
     * Default is null.
     *
     * @var mixed
     */
    private $legacyLayout;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel\URIHelper
     */
    private $uriHelper;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var \eZ\Bundle\EzPublishLegacyBundle\LegacyResponse\LegacyResponseManager
     */
    private $legacyResponseManager;

    public function __construct( \Closure $kernelClosure, ConfigResolverInterface $configResolver, URIHelper $uriHelper, LegacyResponseManager $legacyResponseManager )
    {
        $this->kernel = $kernelClosure();
        $this->legacyLayout = $configResolver->getParameter( 'module_default_layout', 'ezpublish_legacy' );
        $this->configResolver = $configResolver;
        $this->uriHelper = $uriHelper;
        $this->legacyResponseManager = $legacyResponseManager;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    /**
     * Base fallback action.
     * Will be basically used for every legacy module.
     *
     * @return \eZ\Bundle\EzPublishLegacyBundle\LegacyResponse
     */
    public function indexAction()
    {
        $legacyMode = $this->configResolver->getParameter( 'legacy_mode' );
        $this->kernel->setUseExceptions( false );
        // Fix up legacy URI with current request since we can be in a sub-request here.
        $this->uriHelper->updateLegacyURI( $this->request );

        // If we have a layout for legacy AND we're not in legacy mode, we ask the legacy kernel not to generate layout.
        if ( isset( $this->legacyLayout ) && !$legacyMode )
        {
            $this->kernel->setUsePagelayout( false );
        }

        $result = $this->kernel->run();
        $this->kernel->setUseExceptions( true );

        $response = $this->legacyResponseManager->generateResponseFromModuleResult( $result );
        $this->legacyResponseManager->mapHeaders( headers_list(), $response );

        return $response;
    }
}
