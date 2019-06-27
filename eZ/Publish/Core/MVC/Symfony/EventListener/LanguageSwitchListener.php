<?php

/**
 * File containing the LanguageSwitchListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\EventListener;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for language switcher.
 * Will be triggered when generating a RouteReference.
 */
class LanguageSwitchListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    private $translationHelper;

    public function __construct(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::ROUTE_REFERENCE_GENERATION => 'onRouteReferenceGeneration',
        ];
    }

    /**
     * If "language" parameter is present, will try to get corresponding SiteAccess for translation.
     * If found, it will add "siteaccess" parameter to the RouteReference, to trigger SiteAccess switch when generating
     * the final link.
     *
     * @see \eZ\Publish\Core\MVC\Symfony\Routing\Generator::generate()
     * @see \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator::doGenerate()
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent $event
     */
    public function onRouteReferenceGeneration(RouteReferenceGenerationEvent $event)
    {
        $routeReference = $event->getRouteReference();
        if (!$routeReference->has('language')) {
            return;
        }

        $language = $routeReference->get('language');
        $routeReference->remove('language');
        $siteAccess = $this->translationHelper->getTranslationSiteAccess($language);
        if ($siteAccess !== null) {
            $routeReference->set('siteaccess', $siteAccess);
        }
    }
}
