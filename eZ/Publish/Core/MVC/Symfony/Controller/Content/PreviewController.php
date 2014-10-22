<?php
/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Helper\ContentPreviewHelper;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class PreviewController
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $kernel;

    /**
     * @var \eZ\Publish\Core\Helper\ContentPreviewHelper
     */
    private $previewHelper;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(
        ContentService $contentService,
        HttpKernelInterface $kernel,
        ContentPreviewHelper $previewHelper,
        SecurityContextInterface $securityContext
    )
    {
        $this->contentService = $contentService;
        $this->kernel = $kernel;
        $this->previewHelper = $previewHelper;
        $this->securityContext = $securityContext;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    public function previewContentAction( $contentId, $versionNo, $language, $siteAccessName = null )
    {
        try
        {
            $content = $this->contentService->loadContent( $contentId, array( $language ), $versionNo );
            $location = $this->previewHelper->getPreviewLocation( $contentId );
        }
        catch ( UnauthorizedException $e )
        {
            throw new AccessDeniedException();
        }

        if ( !$this->securityContext->isGranted( new AuthorizationAttribute( 'content', 'versionread', array( 'valueObject' => $content ) ) ) )
        {
            throw new AccessDeniedException();
        }

        $siteAccess = $this->previewHelper->getOriginalSiteAccess();
        // Only switch if $siteAccessName is set and different from original
        if ( $siteAccessName !== null && $siteAccessName !== $siteAccess->name )
        {
            $siteAccess = $this->previewHelper->changeConfigScope( $siteAccessName );
        }

        $response = $this->kernel->handle(
            $this->getForwardRequest( $location, $content, $siteAccess ),
            HttpKernelInterface::SUB_REQUEST
        );
        $response->headers->remove( 'cache-control' );
        $response->headers->remove( 'expires' );

        $this->previewHelper->restoreConfigScope();

        return $response;
    }

    /**
     * Returns the Request object that will be forwarded to the kernel for previewing the content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $previewSiteAccess
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getForwardRequest( Location $location, Content $content, SiteAccess $previewSiteAccess )
    {
        return $this->request->duplicate(
            null, null,
            array(
                '_controller' => 'ez_content:viewLocation',
                // specify a route for RouteReference generator
                '_route' => UrlAliasGenerator::INTERNAL_LOCATION_ROUTE,
                '_route_params' => array(
                    'locationId' => $location->id,
                ),
                'location' => $location,
                'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
                'layout' => true,
                'params' => array(
                    'content' => $content,
                    'location' => $location,
                    'isPreview' => true
                ),
                'siteaccess' => $previewSiteAccess,
                'semanticPathinfo' => $this->request->attributes->get( 'semanticPathinfo' ),
            )
        );
    }
}
