<?php

/**
 * File containing the SecurityController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\LoginFormView;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Templating\EngineInterface;

class SecurityController
{
    /** @var \Symfony\Component\Templating\EngineInterface */
    protected $templateEngine;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \Symfony\Component\Security\Http\Authentication\AuthenticationUtils */
    protected $authenticationUtils;

    public function __construct(EngineInterface $templateEngine, ConfigResolverInterface $configResolver, AuthenticationUtils $authenticationUtils)
    {
        $this->templateEngine = $templateEngine;
        $this->configResolver = $configResolver;
        $this->authenticationUtils = $authenticationUtils;
    }

    public function loginAction()
    {
        $view = new LoginFormView($this->configResolver->getParameter('security.login_template'));
        $view->setLastUsername($this->authenticationUtils->getLastUsername());
        $view->setLastAuthenticationError($this->authenticationUtils->getLastAuthenticationError());
        $view->addParameters([
            'layout' => $this->configResolver->getParameter('security.base_layout'),
        ]);

        return $view;
    }
}
