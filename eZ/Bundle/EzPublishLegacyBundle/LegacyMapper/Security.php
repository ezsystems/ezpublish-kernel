<?php
/**
 * File containing the Security class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use ezpWebBasedKernelHandler;
use eZUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * This listener injects current user into legacy kernel once built.
 */
class Security implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    private $enabled = true;

    public function __construct( Repository $repository, ConfigResolverInterface $configResolver, SecurityContextInterface $securityContext )
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->securityContext = $securityContext;
    }

    /**
     * Toggles the feature
     *
     * @param bool $enabled
     */
    public function setEnabled( $enabled )
    {
        $this->enabled = (bool)$enabled;
    }

    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::POST_BUILD_LEGACY_KERNEL => 'onKernelBuilt',
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL_WEB => 'onLegacyKernelWebBuild',
        );
    }

    /**
     * Performs actions related to security once the legacy kernel has been built.
     *
     * @param PostBuildKernelEvent $event
     */
    public function onKernelBuilt( PostBuildKernelEvent $event )
    {
        // Ignore if not in web context, if legacy_mode is active or if user is not authenticated
        if (
            $this->enabled === false
            || !$event->getKernelHandler() instanceof ezpWebBasedKernelHandler
            || $this->configResolver->getParameter( 'legacy_mode' ) === true
            || !$this->isUserAuthenticated()
        )
        {
            return;
        }

        $currentUser = $this->repository->getCurrentUser();
        $event->getLegacyKernel()->runCallback(
            function () use ( $currentUser )
            {
                $legacyUser = eZUser::fetch( $currentUser->id );
                eZUser::setCurrentlyLoggedInUser( $legacyUser, $legacyUser->attribute( 'contentobject_id' ), eZUser::NO_SESSION_REGENERATE );
            },
            false,
            false
        );
    }

    /**
     * @return bool
     */
    private function isUserAuthenticated()
    {
        // IS_AUTHENTICATED_FULLY inherits from IS_AUTHENTICATED_REMEMBERED.
        // User can be either authenticated by providing credentials during current session
        // or by "remember me" if available.
        return $this->securityContext->isGranted( 'IS_AUTHENTICATED_REMEMBERED' );
    }

    /**
     * Performs actions related to security before kernel build (mainly settings injection).
     *
     * @param PreBuildKernelWebHandlerEvent $event
     */
    public function onLegacyKernelWebBuild( PreBuildKernelWebHandlerEvent $event )
    {
        if ( $this->configResolver->getParameter( 'legacy_mode' ) === true )
        {
            return;
        }

        $injectedMergeSettings = $event->getParameters()->get( 'injected-merge-settings', array() );
        $accessRules = array(
            'access;disable',
            'module;user/login',
            'module;user/logout',
        );
        // Merge existing settings with the new ones if needed.
        if ( isset( $injectedMergeSettings['site.ini/SiteAccessRules/Rules'] ) )
        {
            $accessRules = array_merge( $injectedMergeSettings['site.ini/SiteAccessRules/Rules'], $accessRules );
        }
        $injectedMergeSettings['site.ini/SiteAccessRules/Rules'] = $accessRules;
        $event->getParameters()->set( 'injected-merge-settings', $injectedMergeSettings );
    }
}
