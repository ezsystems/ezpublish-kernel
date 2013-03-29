<?php
/**
 * File containing the LegacyKernelController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
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
     * @todo Maybe following dependencies should be mutualized in an abstract controller
     *       Injection can be done through "parent service" feature for DIC : http://symfony.com/doc/master/components/dependency_injection/parentservices.html
     * @param \Closure $kernelClosure
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver     
     * @param mixed $legacyLayout
     */
    public function __construct( \Closure $kernelClosure, EngineInterface $templateEngine, ConfigResolverInterface $configResolver )
    {
        $this->kernel = $kernelClosure();
        $this->templateEngine = $templateEngine;
        $this->legacyLayout = $configResolver->getParameter( 'module_default_layout', 'ezpublish_legacy' );
        $this->configResolver = $configResolver;
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return Response A Response instance
     */
    public function render( $view, array $parameters = array() )
    {
        $response = new Response();
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
        $result = $this->kernel->run();
        $this->kernel->setUseExceptions( true );

        $moduleResult = $result->getAttribute( 'module_result' );

        if ( isset( $this->legacyLayout ) && !$legacyMode )
        {
            return $this->render(
                $this->legacyLayout,
                array( 'module_result' => $moduleResult )
            );
        }

        return new Response(
            $result->getContent()
        );
    }
}
