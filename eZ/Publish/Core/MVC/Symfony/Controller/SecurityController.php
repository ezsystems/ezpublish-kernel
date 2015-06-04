<?php
/**
 * File containing the SecurityController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Templating\EngineInterface;

class SecurityController
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templateEngine;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface|null
     */
    protected $csrfProvider;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function __construct( EngineInterface $templateEngine, ConfigResolverInterface $configResolver, CsrfProviderInterface $csrfProvider = null )
    {
        $this->templateEngine = $templateEngine;
        $this->configResolver = $configResolver;
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

        if ( $this->request->attributes->has( Security::AUTHENTICATION_ERROR ) )
        {
            $error = $this->request->attributes->get( Security::AUTHENTICATION_ERROR );
        }
        else
        {
            $error = $session->get( Security::AUTHENTICATION_ERROR );
            $session->remove( Security::AUTHENTICATION_ERROR );
        }

        $csrfToken = isset( $this->csrfProvider ) ? $this->csrfProvider->generateCsrfToken( 'authenticate' ) : null;
        return new Response(
            $this->templateEngine->render(
                $this->configResolver->getParameter( 'security.login_template' ),
                array(
                    'last_username' => $session->get( Security::LAST_USERNAME ),
                    'error' => $error,
                    'csrf_token' => $csrfToken,
                    'layout' => $this->configResolver->getParameter( 'security.base_layout' ),
                )
            )
        );
    }
}
