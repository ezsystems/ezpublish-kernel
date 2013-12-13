<?php
/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class PreviewController implements SiteAccessAware
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface
     */
    private $viewManager;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    private $localeConverter;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $currentSiteAccess;

    public function __construct(
        Repository $repository,
        ViewManagerInterface $viewManager,
        VersatileScopeInterface $configResolver,
        TranslatorInterface $translator,
        LocaleConverterInterface $localeConverter
    )
    {
        $this->repository = $repository;
        $this->viewManager = $viewManager;
        $this->configResolver = $configResolver;
        $this->translator = $translator;
        $this->localeConverter = $localeConverter;
    }

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->currentSiteAccess = $siteAccess;
    }

    public function previewContentAction( $contentId, $versionNo, $language, $siteAccessName )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        list( $content, $location ) = $this->repository->sudo(
            function ( $repository ) use ( $contentId, $versionNo, $language )
            {
                /** @var \eZ\Publish\API\Repository\Repository $repository */
                $contentService = $repository->getContentService();
                $content = $contentService->loadContent( $contentId, array( $language ), $versionNo );
                $contentInfo = $contentService->loadContentInfo( $contentId );
                // mainLocationId already exists, content has been published at least once.
                if ( $contentInfo->mainLocationId )
                {
                    $location = $repository->getLocationService()->loadLocation( $contentInfo->mainLocationId );
                }
                // Content not yet published, we create a virtual location object.
                else
                {
                    $location = new Location( array( 'contentInfo' => $contentInfo ) );
                }

                return array( $content, $location );
            }
        );

        if ( !$this->repository->canUser( 'content', 'versionview', $content ) )
        {
            throw new AccessDeniedException();
        }

        // ViewManager must be SiteAccessAware to allow view configuration change.
        // TODO: ConfigResolver scope change should be sufficient in the long term. Change this when using proxy services.
        if ( !$this->viewManager instanceof SiteAccessAware )
        {
            throw new InvalidArgumentType( 'ViewManager', 'SiteAccessAware' );
        }

        // Change configuration scope and current locale to trigger proper preview.
        $previousDefaultScope = $this->configResolver->getDefaultScope();
        $previousLocale = $this->translator->getLocale();
        $this->configResolver->setDefaultScope( $siteAccessName );
        $this->viewManager->setSiteAccess( new SiteAccess( $siteAccessName ) );
        $this->translator->setLocale( $this->localeConverter->convertToPOSIX( $language ) );

        $response = new Response( $this->viewManager->renderLocation( $location ) );

        $this->configResolver->setDefaultScope( $previousDefaultScope );
        $this->viewManager->setSiteAccess( $this->currentSiteAccess );
        $this->translator->setLocale( $previousLocale );

        return $response;
    }
}
