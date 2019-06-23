<?php

/**
 * File containing the SessionBasedFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class RestSessionBasedFactory extends FormLoginFactory
{
    public function __construct()
    {
        parent::__construct();
        unset($this->options['check_path']);
        $this->defaultSuccessHandlerOptions = [];
        $this->defaultFailureHandlerOptions = [];
    }

    protected function isRememberMeAware($config)
    {
        return false;
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = $this->getListenerId();
        $listener = new DefinitionDecorator($listenerId);
        $listener->replaceArgument(2, $id);

        /* @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $listenerId .= '.' . $id;
        $container->setDefinition($listenerId, $listener);
        $container->setAlias('ezpublish_rest.session_authenticator', $listenerId);

        if ($container->hasDefinition('security.logout_listener.' . $id)) {
            // Copying logout handlers to REST session authenticator, to allow proper logout using it.
            $logoutListenerDef = $container->getDefinition('security.logout_listener.' . $id);
            $logoutListenerDef->addMethodCall(
                'addHandler',
                [new Reference('ezpublish_rest.security.authentication.logout_handler')]
            );

            foreach ($logoutListenerDef->getMethodCalls() as $callArray) {
                if ($callArray[0] !== 'addHandler') {
                    continue;
                }

                $listener->addMethodCall('addLogoutHandler', $callArray[1]);
            }
        }

        return $listenerId;
    }

    protected function getListenerId()
    {
        return 'ezpublish_rest.security.authentication.listener.session';
    }

    public function getPosition()
    {
        return 'http';
    }

    public function getKey()
    {
        return 'ezpublish_rest_session';
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        return $defaultEntryPoint;
    }
}
