<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * If the request has an `ez_in_context_translation` cookie, sets the request accept-language
 * to the pseudo-locale used to trigger Crowdin's in-context translation UI.
 */
class CrowdinRequestLocaleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['setInContextAcceptLanguage', 100],
            ],
        ];
    }

    public function setInContextAcceptLanguage(GetResponseEvent $e)
    {
        if (!$e->getRequest()->cookies->has('ez_in_context_translation')) {
            return;
        }

        $e->getRequest()->headers->set('accept-language', 'ach-UG');
    }
}
