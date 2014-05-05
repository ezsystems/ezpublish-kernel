<?php
/**
 * File containing the LanguageSwitchListener class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\EventListener;

use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for language switcher.
 * Will be triggered when generating a RouteReference
 */
class LanguageSwitchListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    private $translationHelper;

    public function __construct( TranslationHelper $translationHelper )
    {
        $this->translationHelper = $translationHelper;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::ROUTE_REFERENCE_GENERATION => 'onRouteReferenceGeneration'
        );
    }

    public function onRouteReferenceGeneration( RouteReferenceGenerationEvent $event )
    {
        $routeReference = $event->getRouteReference();
        if ( !$routeReference->has( 'language' ) )
        {
            return;
        }

        $language = $routeReference->get( 'language' );
        $routeReference->remove( 'language' );
        $siteAccess = $this->translationHelper->getTranslationSiteAccess( $language );
        if ( $siteAccess !== null )
        {
            $routeReference->set( 'siteaccess', $siteAccess );
        }
    }
}
