<?php
/**
 * File containing the LegacyResponseManager class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;

use eZ\Bundle\EzPublishLegacyBundle\LegacyRedirectResponse;
use eZ\Bundle\EzPublishLegacyBundle\LegacyResponse;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;
use ezpKernelResult;
use ezpKernelRedirect;
use DateTime;

/**
 * Utility class to manage Response from legacy controllers, map headers...
 */
class LegacyResponseManager
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templateEngine;

    /**
     * Template declaration to wrap legacy responses in a Twig pagelayout (optional)
     * Either a template declaration string or null/false to use legacy pagelayout
     * Default is null.
     *
     * @var string|null
     */
    private $legacyLayout;

    /**
     * Flag indicating if we're running in legacy mode or not.
     *
     * @var bool
     */
    private $legacyMode;

    public function __construct( EngineInterface $templateEngine, ConfigResolverInterface $configResolver )
    {
        $this->templateEngine = $templateEngine;
        $this->legacyLayout = $configResolver->getParameter( 'module_default_layout', 'ezpublish_legacy' );
        $this->legacyMode = $configResolver->getParameter( 'legacy_mode' );
    }

    /**
     * Generates LegacyResponse object from result returned by legacy kernel.
     *
     * @param \ezpKernelResult $result
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \eZ\Bundle\EzPublishLegacyBundle\LegacyResponse
     */
    public function generateResponseFromModuleResult( ezpKernelResult $result )
    {
        $moduleResult = $result->getAttribute( 'module_result' );

        if ( isset( $this->legacyLayout ) && !$this->legacyMode && !isset( $moduleResult['pagelayout'] ) )
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
            // If having an "Unauthorized" or "Forbidden" error code, we send an AccessDeniedException
            // to be able to trigger redirection to login in Symfony stack.
            if ( $moduleResult['errorCode'] == 401 || $moduleResult['errorCode'] == 403 )
            {
                $errorMessage = isset( $moduleResult['errorMessage'] ) ? $moduleResult['errorMessage'] : 'Access denied';
                throw new AccessDeniedException( $errorMessage );
            }

            $response->setStatusCode(
                $moduleResult['errorCode'],
                isset( $moduleResult['errorMessage'] ) ? $moduleResult['errorMessage'] : null
            );
        }

        return $response;
    }

    /**
     * Generates proper LegacyRedirectResponse from $redirectResult.
     *
     * @param \ezpKernelRedirect $redirectResult
     *
     * @return LegacyRedirectResponse
     */
    public function generateRedirectResponse( ezpKernelRedirect $redirectResult )
    {
        return new LegacyRedirectResponse( $redirectResult->getTargetUrl(), $redirectResult->getStatusCode() );
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return \eZ\Bundle\EzPublishLegacyBundle\LegacyResponse A LegacyResponse instance
     */
    private function render( $view, array $parameters = array() )
    {
        $response = new LegacyResponse();
        $response->setContent( $this->templateEngine->render( $view, $parameters ) );

        return $response;
    }

    /**
     * Sets the response headers from an array of legacy headers.
     *
     * The array of legacy headers reads as follow: array('X-Foo: Bar', ...)
     *
     * @param LegacyResponse|LegacyRedirectResponse $response
     * @param array $headers Array of legacy headers.
     */
    public static function updateLegacyHeaders( $response, array $headers )
    {
        foreach ( $headers as $header )
        {
            $headerArray = explode( ': ', $header, 2 );
            $headerName = strtolower( $headerArray[0] );
            $headerValue = $headerArray[1];
            // Removing existing header to avoid duplicate values
            $response->removeHeader( $headerName );

            switch ( $headerName )
            {
                // max-age and s-maxage are skipped because they are values of the cache-control header
                case 'etag':
                    $response->setEtag( $headerValue );
                    break;
                case 'last-modified':
                    $response->setLastModified( new DateTime( $headerValue ) );
                    break;
                case 'expires':
                    $response->setExpires( new DateTime( $headerValue ) );
                    break;
                default;
                    $response->headers->set( $headerName, $headerValue, true );
                    break;
            }
        }
    }
}
