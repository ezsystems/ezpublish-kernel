<?php
/**
 * File containing the LegacyKernelController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use DateTime;
use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;
use eZ\Publish\Core\MVC\Legacy\Kernel\URIHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
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
     * @todo Maybe following dependencies should be mutualized in an abstract controller
     *       Injection can be done through "parent service" feature for DIC : http://symfony.com/doc/master/components/dependency_injection/parentservices.html
     *
     * @param \Closure $kernelClosure
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \eZ\Publish\Core\MVC\Legacy\Kernel\URIHelper $uriHelper
     */
    public function __construct( \Closure $kernelClosure, EngineInterface $templateEngine, ConfigResolverInterface $configResolver, URIHelper $uriHelper )
    {
        $this->kernel = $kernelClosure();
        $this->templateEngine = $templateEngine;
        $this->legacyLayout = $configResolver->getParameter( 'module_default_layout', 'ezpublish_legacy' );
        $this->configResolver = $configResolver;
        $this->uriHelper = $uriHelper;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return LegacyResponse A LegacyResponse instance
     */
    public function render( $view, array $parameters = array() )
    {
        $response = new LegacyResponse();
        $response->setContent( $this->templateEngine->render( $view, $parameters ) );
        return $response;
    }

    /**
     * Base fallback action.
     * Will be basically used for every legacy module.
     *
     * @return \Symfony\Component\HttpFoundation\Response
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

        $moduleResult = $result->getAttribute( 'module_result' );

        if ( isset( $this->legacyLayout ) && !$legacyMode && !isset( $moduleResult['pagelayout'] ) )
        {
            // Replace original module_result content by filtered one
            $moduleResult['content'] = $result->getContent();

            $response = $this->render(
                $this->legacyLayout,
                array( 'module_result' => $moduleResult )
            );

            $response->setModuleResult( $moduleResult );
        }
        else
        {
            $response = new LegacyResponse( $result->getContent() );
        }

        // Handling error codes sent by the legacy stack
        if ( isset( $moduleResult['errorCode'] ) )
        {
            $response->setStatusCode(
                $moduleResult['errorCode'],
                isset( $moduleResult['errorMessage'] ) ? $moduleResult['errorMessage'] : null
            );
        }

        // Handling headers sent by the legacy stack
        foreach ( headers_list() as $header )
        {
            $headerArray = explode( ": ", $header, 2 );
            $headerName = strtolower( $headerArray[0] );
            $headerValue = $headerArray[1];

            switch ( $headerName )
            {
                // max-age and s-maxage are skipped because they are values of the cache-control header
                case "etag":
                    $response->setEtag( $headerValue );
                    break;
                case "last-modified":
                    $response->setLastModified( new DateTime( $headerValue ) );
                    break;
                case "expires":
                    $response->setExpires( new DateTime( $headerValue ) );
                    break;
                default;
                    $response->headers->set( $headerName, $headerValue, true );
                    break;
            }
        }

        return $response;
    }
}
