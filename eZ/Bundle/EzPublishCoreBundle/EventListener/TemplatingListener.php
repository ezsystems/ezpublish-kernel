<?php
/**
 * File containing the TemplatingListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Liip\ThemeBundle\ActiveTheme;
use Liip\ThemeBundle\Locator\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TemplatingListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Liip\ThemeBundle\ActiveTheme
     */
    private $activeTheme;

    /**
     * @var \Liip\ThemeBundle\Locator\FileLocator
     */
    private $themeFileLocator;

    /**
     * Bundles that are registered known to contain theme resources (templates or assets).
     * Key is the bundle name, value is the absolute path to the bundle directory.
     *
     * @var array
     */
    private $themeBundles;

    public function __construct(
        ContainerInterface $container,
        ActiveTheme $activeTheme,
        FileLocator $themeFileLocator,
        array $themeBundles = array()
    )
    {
        $this->container = $container;
        $this->activeTheme = $activeTheme;
        $this->themeFileLocator = $themeFileLocator;
        $this->themeBundles = $themeBundles;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 200 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
            return;

        $configResolver = $this->container->get( 'ezpublish.config.resolver' );
        $currentTheme = $configResolver->getParameter( 'themes.active_theme' );
        // Skip if there is no active theme defined.
        if ( !$currentTheme )
            return;

        $themeList = $configResolver->getParameter( 'themes.list' );
        $this->activeTheme->setThemes( $themeList );
        $this->activeTheme->setName( $currentTheme );

        // TODO: This order is probably wrong, check with spec.
        $bundleResource = array( '%bundle_path%/Resources/themes/%current_theme%/%template%' );
        // Adding current theme from registered bundles
        foreach ( $this->themeBundles as $bundlePath )
        {
            $bundleResource[] = "$bundlePath/Resources/views/themes/%current_theme%/%template%";
        }

        // Adding theme fallbacks from current bundle
        foreach ( $themeList as $theme )
        {
            if ( $theme == $currentTheme )
                continue;

            $bundleResource[] = "%bundle_path%/Resources/themes/$theme/%template%";
        }

        // Adding fallbacks themes from registered bundles.
        foreach ( $this->themeBundles as $bundlePath )
        {
            foreach ( $themeList as $theme )
            {
                if ( $theme == $currentTheme )
                    continue;

                $bundleResource[] = "$bundlePath/Resources/views/themes/$theme/%template%";
            }
        }

        // Adding theme fallback paths for global application override (ezpublish/Resources/)
        $appResource = array( '%dir%/themes/%current_theme%/%bundle_name%/%template%' );
        foreach ( $themeList as $theme )
        {
            if ( $theme == $currentTheme )
                continue;

            $appResource[] = "%dir%/themes/$theme/%template%";
        }
        $appResource[] = '%dir%/%bundle_name%/%override_path%';
    }
}
