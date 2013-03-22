<?php
/**
 * File containing the LocaleListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Symfony\Component\HttpKernel\EventListener\LocaleListener as BaseRequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Enhanced LocaleListener, injecting the converted locale extracted from eZ Publish configuration.
 */
class LocaleListener extends BaseRequestListener
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    private $localeConverter;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setServiceContainer( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     */
    public function setLocaleConverter( LocaleConverterInterface $localeConverter )
    {
        $this->localeConverter = $localeConverter;
    }

    /**
     * Returns the config resolver.
     * It uses the service container for lazy loading purpose.
     *
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private function getConfigResolver()
    {
        return $this->container->get( 'ezpublish.config.resolver' );
    }

    public function onKernelRequest( GetResponseEvent $event )
    {
        $request = $event->getRequest();
        if ( !$request->attributes->has( '_locale' ) )
        {
            foreach ( $this->getConfigResolver()->getParameter( 'languages' ) as $locale )
            {
                $convertedLocale = $this->localeConverter->convertToPOSIX( $locale );
                if ( $convertedLocale !== null )
                {
                    // Setting the converted locale to the _locale request attribute, so that it can be properly processed by parent listener.
                    $request->attributes->set( '_locale', $convertedLocale );
                    break;
                }
            }
        }

        parent::onKernelRequest( $event );
    }
}
