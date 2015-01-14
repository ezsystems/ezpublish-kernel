<?php
/**
 * This file is part of the EzPublishLegacyBridge package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Cache;

use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;

/**
 * A GatewayCachePurger decorator that allows the actual purger to be switched on/off.
 */
class SwitchableHttpCachePurger implements GatewayCachePurger
{
    use Switchable;

    /** @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger */
    private $gatewayCachePurger;

    public function __construct( GatewayCachePurger $gatewayCachePurger )
    {
        $this->gatewayCachePurger = $gatewayCachePurger;
    }

    public function purge( $cacheElements )
    {
        if ( $this->getSwitch() === false )
        {
            return;
        }

        $this->gatewayCachePurger->purge( $cacheElements );
    }

    public function purgeAll()
    {
        if ( $this->getSwitch() === false )
        {
            return;
        }

        $this->gatewayCachePurger->purgeAll();
    }
}
