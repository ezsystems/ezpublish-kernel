<?php

/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Helper\ContentPreviewHelper;
use eZ\Publish\Core\Helper\PreviewLocationProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\ContentViewInterface;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreviewController
{
    const INTERNAL_LOCATION_VIEW_ROUTE = '_ezpublishLocation';

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
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \eZ\Publish\Core\Helper\PreviewLocationProvider
     */
    private $locationProvider;

    public function __construct(
        ContentService $contentService,
        HttpKernelInterface $kernel,
        ContentPreviewHelper $previewHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        PreviewLocationProvider $locationProvider
    ) {
        $this->contentService = $contentService;
        $this->kernel = $kernel;
        $this->previewHelper = $previewHelper;
        $this->authorizationChecker = $authorizationChecker;
        $this->locationProvider = $locationProvider;
    }

    /**
     * @throws NotImplementedException If Content is missing location as this is not supported in current version
     */
    public function previewContentAction(Request $request, $contentId, $versionNo, $language, $siteAccessName = null)
    {
        $this->previewHelper->setPreviewActive(true);

        try {
            $content = $this->contentService->loadContent($contentId, array($language), $versionNo);
            $location = $this->locationProvider->loadMainLocation($contentId);

            if (!$location instanceof Location) {
                throw new NotImplementedException('Preview for content without locations');
            }

            $this->previewHelper->setPreviewedContent($content);
            $this->previewHelper->setPreviewedLocation($location);
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        }

        if (!$this->authorizationChecker->isGranted(new AuthorizationAttribute('content', 'versionread', array('valueObject' => $content)))) {
            throw new AccessDeniedException();
        }

        $siteAccess = $this->previewHelper->getOriginalSiteAccess();
        // Only switch if $siteAccessName is set and different from original
        if ($siteAccessName !== null && $siteAccessName !== $siteAccess->name) {
            $siteAccess = $this->previewHelper->changeConfigScope($siteAccessName);
        }

        $response = $this->kernel->handle(
            $this->getForwardRequest($location, $content, $siteAccess, $request),
            HttpKernelInterface::SUB_REQUEST
        );
        $response->headers->remove('cache-control');
        $response->headers->remove('expires');

        $this->previewHelper->restoreConfigScope();
        $this->previewHelper->setPreviewActive(false);

        return $response;
    }

    /**
     * Returns the Request object that will be forwarded to the kernel for previewing the content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $previewSiteAccess
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getForwardRequest(Location $location, Content $content, SiteAccess $previewSiteAccess, Request $request)
    {
        $forwardRequestParameters = array(
            '_controller' => 'ez_content:viewContent',
            // specify a route for RouteReference generator
            '_route' => UrlAliasGenerator::INTERNAL_CONTENT_VIEW_ROUTE,
            '_route_params' => array(
                'contentId' => $content->id,
                'locationId' => $location->id,
            ),
            'location' => $location,
            'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
            'layout' => true,
            'params' => array(
                'content' => $content,
                'location' => $location,
                'isPreview' => true,
            ),
            'siteaccess' => $previewSiteAccess,
            'semanticPathinfo' => $request->attributes->get('semanticPathinfo'),
        );

        if ($this->usesCustomController($location)) {
            $forwardRequestParameters = [
                '_controller' => 'ez_content:viewLocation',
                '_route' => self::INTERNAL_LOCATION_VIEW_ROUTE,
            ] + $forwardRequestParameters;
        }

        return $request->duplicate(
            null,
            null,
            $forwardRequestParameters
        );
    }

    /**
     * @param $viewProviders \eZ\Publish\Core\MVC\Symfony\View\Provider\Location[]
     */
    public function addLocationViewProviders(array $viewProviders)
    {
        $this->viewProviders = $viewProviders;
    }

    /**
     * Tests if $location has match a view that uses a custom controller.
     *
     * @since 5.4.5
     *
     * @param $location Location
     *
     * @return bool
     */
    private function usesCustomController(Location $location)
    {
        foreach ($this->viewProviders as $viewProvider) {
            $view = $viewProvider->getView($location, 'full');
            if ($view instanceof ContentViewInterface) {
                $configHash = $view->getConfigHash();
                if (isset($configHash['controller'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
