<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Security;

use eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener as BaseListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SecurityListener extends BaseListener
{
    public function onKernelRequest( GetResponseEvent $event )
    {
        // In legacy_mode, roles and policies must be delegated to legacy kernel.
        if ( $this->configResolver->getParameter( 'legacy_mode' ) )
        {
            return;
        }

        parent::onKernelRequest( $event );
    }
}
