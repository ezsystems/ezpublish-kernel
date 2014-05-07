<?php
/**
 * File containing the ConfigScopeListener class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\Core\MVC\Legacy\Kernel\Loader;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigScopeListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel\Loader
     */
    private $kernelLoader;

    public function __construct( Loader $kernelLoader )
    {
        $this->kernelLoader = $kernelLoader;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::CONFIG_SCOPE_CHANGE => 'onConfigScopeChange',
            MVCEvents::CONFIG_SCOPE_RESTORE => 'onConfigScopeChange',
        );
    }

    public function onConfigScopeChange( ScopeChangeEvent $event )
    {
        $this->kernelLoader->resetKernel();
    }
}
