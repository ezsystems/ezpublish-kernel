<?php
/**
 * File containing the Security class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Legacy\Event\PostBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use ezpWebBasedKernelHandler;
use eZUser;
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

    public function __construct( Repository $repository )
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::POST_BUILD_LEGACY_KERNEL => 'onKernelBuilt'
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
        )
        {
            return;
        }

        $currentUser = $this->repository->getCurrentUser();
        $event->getLegacyKernel()->runCallback(
            function () use ( $currentUser )
            {
                $legacyUser = eZUser::fetch( $currentUser->id );
                eZUser::setCurrentlyLoggedInUser( $legacyUser, $legacyUser->attribute( 'contentobject_id' ) );
            },
            false
        );
    }
}
