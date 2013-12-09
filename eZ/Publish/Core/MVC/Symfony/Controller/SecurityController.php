<?php
/**
 * File containing the SecurityControler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;

class SecurityController
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    /**
     * @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface|null
     */
    protected $csrfProvider;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function __construct( EngineInterface $templateEngine, CsrfProviderInterface $csrfProvider = null )
    {
        $this->templateEngine = $templateEngine;
        $this->csrfProvider = $csrfProvider;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    public function loginAction()
    {
        $session = $this->request->getSession();

        if ( $this->request->attributes->has( SecurityContextInterface::AUTHENTICATION_ERROR ) )
        {
            $error = $this->request->attributes->get( SecurityContextInterface::AUTHENTICATION_ERROR );
        }
        else
        {
            $error = $session->get( SecurityContextInterface::AUTHENTICATION_ERROR );
            $session->remove( SecurityContextInterface::AUTHENTICATION_ERROR );
        }

        $csrfToken = isset( $this->csrfProvider ) ? $this->csrfProvider->generateCsrfToken( 'authenticate' ) : null;
        return new Response(
            $this->templateEngine->render(
                'EzPublishCoreBundle:Security:login.html.twig',
                array(
                    'last_username' => $session->get( SecurityContextInterface::LAST_USERNAME ),
                    'error' => $error,
                    'csrf_token' => $csrfToken,
                )
            )
        );
    }
}
