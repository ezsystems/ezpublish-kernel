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
use ezpModuleNotFound;
use ezpModuleViewNotFound;
use ezpModuleDisabled;
use ezpModuleViewDisabled;
use ezpAccessDenied;
use ezpContentNotFoundException;
use ezpLanguageNotFound;

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
     * @todo Maybe following dependencies should be mutualized in an abstract controller
     *       Injection can be done through "parent service" feature for DIC : http://symfony.com/doc/master/components/dependency_injection/parentservices.html
     * @param \Closure $kernelClosure
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     */
    public function __construct( \Closure $kernelClosure, EngineInterface $templateEngine )
    {
        $this->kernel = $kernelClosure();
        $this->templateEngine = $templateEngine;
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
        $this->kernel->setUseExceptions( false );
        $result = $this->kernel->run();
        $this->kernel->setUseExceptions( true );

        return new Response(
            $result->getContent()
        );
    }
}
