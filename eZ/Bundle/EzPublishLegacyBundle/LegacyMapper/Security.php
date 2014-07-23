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
use eZSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This listener injects current user into legacy kernel once built.
 */
class Security implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    private $enabled = true;

    public function __construct( Repository $repository, ConfigResolverInterface $configResolver )
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
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
        // Ignore if not in web context or if legacy_mode is active.
        if (
            !$event->getKernelHandler() instanceof ezpWebBasedKernelHandler
            || $this->configResolver->getParameter( 'legacy_mode' ) === true
            || $this->enabled === false
        )
        {
            return;
        }

        $currentUserId = $this->repository->getCurrentUser()->id;

        $event->getLegacyKernel()->runCallback(
            function () use ( $currentUserId )
            {
                if ( $currentUserId == eZUser::anonymousId() && !eZSession::hasStarted() )
                {
                    return;
                }
                $legacyUser = eZUser::fetch( $currentUserId );
                eZUser::setCurrentlyLoggedInUser( $legacyUser, $legacyUser->attribute( 'contentobject_id' ), eZUser::NO_SESSION_REGENERATE );
            },
            false
        );
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
