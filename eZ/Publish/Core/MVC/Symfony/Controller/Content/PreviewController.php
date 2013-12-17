<?php
/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class PreviewController implements SiteAccessAware
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    private $repository;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $kernel;

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

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(
        Repository $repository,
        HttpKernelInterface $kernel,
        ViewManagerInterface $viewManager,
        VersatileScopeInterface $configResolver,
        TranslatorInterface $translator,
        LocaleConverterInterface $localeConverter
    )
    {
        $this->repository = $repository;
        $this->kernel = $kernel;
        $this->viewManager = $viewManager;
        $this->configResolver = $configResolver;
        $this->translator = $translator;
        $this->localeConverter = $localeConverter;
    }

    public function setSiteAccess( SiteAccess $siteAccess = null )
    {
        $this->currentSiteAccess = $siteAccess;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    public function previewContentAction( $contentId, $versionNo, $language, $siteAccessName )
    {
        try
        {
            // Change configuration scope and current locale to trigger proper preview.
            $previousDefaultScope = $this->configResolver->getDefaultScope();
            $this->configResolver->setDefaultScope( $siteAccessName );

            $contentService = $this->repository->getContentService();
            $content = $contentService->loadContent( $contentId, array( $language ), $versionNo );
            $contentInfo = $contentService->loadContentInfo( $contentId );
            // mainLocationId already exists, content has been published at least once.
            if ( $contentInfo->mainLocationId )
            {
                $location = $this->repository->getLocationService()->loadLocation( $contentInfo->mainLocationId );
            }
            // Content not yet published, we create a virtual location object.
            else
            {
                $location = new Location( array( 'contentInfo' => $contentInfo ) );
            }
        }
        catch ( UnauthorizedException $e )
        {
            throw new AccessDeniedException();
        }

        if ( !$this->repository->canUser( 'content', 'versionview', $content ) )
        {
            throw new AccessDeniedException();
        }

        // ViewManager must be SiteAccessAware to allow view configuration change.
        // TODO: ConfigResolver scope change should be sufficient in the long term. Change this when using proxy services.
        // TODO: Don't use viewManager for this, prefer injecting siteaccess directly in matcher factories
        if ( !$this->viewManager instanceof SiteAccessAware )
        {
            throw new InvalidArgumentType( 'ViewManager', 'SiteAccessAware' );
        }

        $this->viewManager->setSiteAccess( new SiteAccess( $siteAccessName ) );

        $response = $this->kernel->handle(
            $this->request->duplicate(
                null, null,
                array(
                    '_controller' => 'ez_content:viewLocation',
                    'location' => $location,
                    'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                    'layout' => true
                )
            ),
            HttpKernelInterface::SUB_REQUEST
        );

        $this->configResolver->setDefaultScope( $previousDefaultScope );
        $this->viewManager->setSiteAccess( $this->currentSiteAccess );

        return $response;
    }
}
