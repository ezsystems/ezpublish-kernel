<?php

/**
 * File containing the PreviewController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use Exception;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Helper\ContentPreviewHelper;
use eZ\Publish\Core\Helper\PreviewLocationProvider;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\View\CustomLocationControllerChecker;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PreviewController
{
    const PREVIEW_PARAMETER_NAME = 'isPreview';
    const INTERNAL_LOCATION_VIEW_ROUTE = '_ezpublishLocation';

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface */
    private $kernel;

    /** @var \eZ\Publish\Core\Helper\ContentPreviewHelper */
    private $previewHelper;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\CustomLocationControllerChecker */
    private $controllerChecker;

    public function __construct(
        ContentService $contentService,
        HttpKernelInterface $kernel,
        ContentPreviewHelper $previewHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        PreviewLocationProvider $locationProvider,
        CustomLocationControllerChecker $controllerChecker
    ) {
        $this->contentService = $contentService;
        $this->kernel = $kernel;
        $this->previewHelper = $previewHelper;
        $this->authorizationChecker = $authorizationChecker;
        $this->locationProvider = $locationProvider;
        $this->controllerChecker = $controllerChecker;
    }

    /**
     * @throws NotImplementedException If Content is missing location as this is not supported in current version
     */
    public function previewContentAction(Request $request, $contentId, $versionNo, $language, $siteAccessName = null)
    {
        $this->previewHelper->setPreviewActive(true);

        try {
            $content = $this->contentService->loadContent($contentId, [$language], $versionNo);
            $location = $this->locationProvider->loadMainLocationByContent($content);

            if (!$location instanceof Location) {
                throw new NotImplementedException('Preview for content without locations');
            }

            $this->previewHelper->setPreviewedContent($content);
            $this->previewHelper->setPreviewedLocation($location);
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        }

        if (!$this->authorizationChecker->isGranted(new AuthorizationAttribute('content', 'versionread', ['valueObject' => $content]))) {
            throw new AccessDeniedException();
        }

        $siteAccess = $this->previewHelper->getOriginalSiteAccess();
        // Only switch if $siteAccessName is set and different from original
        if ($siteAccessName !== null && $siteAccessName !== $siteAccess->name) {
            $siteAccess = $this->previewHelper->changeConfigScope($siteAccessName);
        }

        try {
            $response = $this->kernel->handle(
                $this->getForwardRequest($location, $content, $siteAccess, $request, $language),
                HttpKernelInterface::SUB_REQUEST,
                false
            );
        } catch (\Exception $e) {
            if ($location->isDraft() && $this->controllerChecker->usesCustomController($content, $location)) {
                // @todo This should probably be an exception that embeds the original one
                $message = <<<EOF
<p>The view that rendered this location draft uses a custom controller, and resulted in a fatal error.</p>
<p>Location View is deprecated, as it causes issues with preview, such as an empty location id when previewing the first version of a content.</p>
EOF;

                throw new Exception($message, 0, $e);
            } else {
                throw $e;
            }
        }
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
     * @param string $language
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getForwardRequest(Location $location, Content $content, SiteAccess $previewSiteAccess, Request $request, $language)
    {
        $forwardRequestParameters = [
            '_controller' => UrlAliasRouter::VIEW_ACTION,
            // specify a route for RouteReference generator
            '_route' => UrlAliasGenerator::INTERNAL_CONTENT_VIEW_ROUTE,
            '_route_params' => [
                'contentId' => $content->id,
                'locationId' => $location->id,
            ],
            'location' => $location,
            'content' => $content,
            'viewType' => ViewManagerInterface::VIEW_TYPE_FULL,
            'layout' => true,
            'params' => [
                'content' => $content,
                'location' => $location,
                self::PREVIEW_PARAMETER_NAME => true,
                'language' => $language,
            ],
            'siteaccess' => $previewSiteAccess,
            'semanticPathinfo' => $request->attributes->get('semanticPathinfo'),
        ];

        if ($this->controllerChecker->usesCustomController($content, $location)) {
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
}
