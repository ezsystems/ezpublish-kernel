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
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\Repository\Values\Content\Location;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PreviewController implements SiteAccessAware
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

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
        VersatileScopeInterface $configResolver
    )
    {
        $this->repository = $repository;
        $this->contentService = $this->repository->getContentService();
        $this->locationService = $this->repository->getLocationService();
        $this->kernel = $kernel;
        $this->configResolver = $configResolver;

        // ViewManager must be SiteAccessAware to allow view configuration change.
        if ( !$viewManager instanceof SiteAccessAware )
        {
            throw new InvalidArgumentType( 'ViewManager', 'SiteAccessAware' );
        }
        $this->viewManager = $viewManager;
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
            $content = $this->contentService->loadContent( $contentId, array( $language ), $versionNo );
            $location = $this->getPreviewLocation( $contentId );
        }
        catch ( UnauthorizedException $e )
        {
            throw new AccessDeniedException();
        }

        if ( !$this->repository->canUser( 'content', 'versionview', $content ) )
        {
            throw new AccessDeniedException();
        }

        // Change configuration scope and current locale to trigger proper preview.
        $previousDefaultScope = $this->configResolver->getDefaultScope();
        $this->configResolver->setDefaultScope( $siteAccessName );
        $this->viewManager->setSiteAccess( new SiteAccess( $siteAccessName ) );

        $response = $this->kernel->handle(
            $this->request->duplicate(
                null, null,
                array(
                    '_controller' => 'ez_content:viewLocation',
                    'location' => $location,
                    'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                    'layout' => true,
                    'params' => array( 'content' => $content, 'location' => $location )
                )
            ),
            HttpKernelInterface::SUB_REQUEST
        );
        $response->headers->removeCacheControlDirective( 's-maxage' );

        $this->configResolver->setDefaultScope( $previousDefaultScope );
        $this->viewManager->setSiteAccess( $this->currentSiteAccess );

        return $response;
    }

    /**
     * Returns a valid Location object for $contentId.
     * Will either load mainLocationId (if available) or build a virtual Location object.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location|Location
     */
    private function getPreviewLocation( $contentId )
    {
        // contentInfo must be reloaded content is not published yet (e.g. no mainLocationId)
        $contentInfo = $this->contentService->loadContentInfo( $contentId );
        // mainLocationId already exists, content has been published at least once.
        if ( $contentInfo->mainLocationId )
        {
            $location = $this->locationService->loadLocation( $contentInfo->mainLocationId );
        }
        // Content not yet published, we create a virtual location object.
        else
        {
            $location = new Location( array( 'contentInfo' => $contentInfo ) );
        }

        return $location;
    }
}
