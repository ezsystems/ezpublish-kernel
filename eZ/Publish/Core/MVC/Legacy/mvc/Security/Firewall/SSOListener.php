<?php
/**
 * File containing the SSOListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Security\Firewall;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractPreAuthenticatedListener;
use eZINI;
use eZUser;

/**
 * Firewall listener for legacy SSO handlers.
 */
class SSOListener extends AbstractPreAuthenticatedListener
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    /**
     * @param \Closure $legacyKernelClosure
     */
    public function setLegacyKernelClosure( \Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
    }

    /**
     * @param mixed $userService
     */
    public function setUserService( UserService $userService )
    {
        $this->userService = $userService;
    }

    /**
     * Iterates over legacy SSO handlers, and pre-authenticates a user if a handler returns one.
     *
     * @param Request $request A Request instance
     *
     * @return array An array composed of the user and the credentials
     */
    protected function getPreAuthenticatedData( Request $request )
    {
        $kernelClosure = $this->legacyKernelClosure;
        /** @var \ezpKernelHandler $legacyKernel */
        $legacyKernel = $kernelClosure();
        $logger = $this->logger;

        $legacyUser = $legacyKernel->runCallback(
            function () use ( $logger )
            {
                foreach ( eZINI::instance()->variable( 'UserSettings', 'SingleSignOnHandlerArray' ) as $ssoHandlerName )
                {
                    $className = 'eZ' . $ssoHandlerName . 'SSOHandler';
                    if ( !class_exists( $className ) )
                    {
                        if ( $logger )
                        {
                            $logger->error( "Undefined legacy SSOHandler class: $className" );
                        }
                        continue;
                    }

                    $ssoHandler = new $className();
                    $ssoUser = $ssoHandler->handleSSOLogin();
                    if ( !$ssoUser instanceof eZUser )
                    {
                        continue;
                    }

                    $logger->info( "Matched user using eZ legacy SSO Handler: $className" );
                    return $ssoUser;
                }
            },
            false,
            false
        );

        // No matched user with legacy.
        if ( !$legacyUser instanceof eZUser )
        {
            return array( '', '' );
        }

        $user = new User(
            $this->userService->loadUser( $legacyUser->attribute( 'contentobject_id' ) ),
            array( 'ROLE_USER' )
        );
        return array( $user, $user->getPassword() );
    }
}
