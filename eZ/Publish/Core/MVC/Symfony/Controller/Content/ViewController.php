<?php

/**
 * File containing the ViewController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\APIContentExceptionEvent;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DateTime;
use Exception;

/**
 * This controller provides the content view feature.
 *
 * @since 6.0.0 All methods except `view()` are deprecated and will be removed in the future.
 */
class ViewController extends Controller
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface */
    protected $viewManager;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(ViewManagerInterface $viewManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->viewManager = $viewManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * This is the default view action or a ContentView object.
     *
     * It doesn't do anything by itself: the returned View object is rendered by the ViewRendererListener
     * into an HttpFoundation Response.
     *
     * This action can be selectively replaced by a custom action by means of content_view
     * configuration. Custom actions can add parameters to the view and customize the Response the View will be
     * converted to. They may also bypass the ViewRenderer by returning an HttpFoundation Response.
     *
     * Cache is in both cases handled by the CacheViewResponseListener.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    public function viewAction(ContentView $view)
    {
        return $view;
    }

    /**
     * Embed a content.
     * Behaves mostly like viewAction(), but with specific content load permission handling.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */
    public function embedAction(ContentView $view)
    {
        return $view;
    }

    /**
     * Build the response so that depending on settings it's cacheable.
     *
     * @param string|null $etag
     * @param \DateTime|null $lastModified
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse($etag = null, DateTime $lastModified = null)
    {
        $request = $this->getRequest();
        $response = new Response();
        if ($this->getParameter('content.view_cache') === true) {
            $response->setPublic();
            if ($etag !== null) {
                $response->setEtag($etag);
            }

            if ($this->getParameter('content.ttl_cache') === true) {
                $response->setSharedMaxAge(
                    $this->getParameter('content.default_ttl')
                );
            }

            // Make the response vary against X-User-Hash header ensures that an HTTP
            // reverse proxy caches the different possible variations of the
            // response as it can depend on user role for instance.
            if ($request->headers->has('X-User-Hash')) {
                $response->setVary('X-User-Hash');
            }

            if ($lastModified != null) {
                $response->setLastModified($lastModified);
            }
        }

        return $response;
    }

    /**
     * Main action for viewing content through a location in the repository.
     * Response will be cached with HttpCache validation model (Etag).
     *
     * @param int $locationId
     * @param string $viewType
     * @param bool $layout
     * @param array $params
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated Since 6.0.0. Viewing locations is now done with ViewContent.
     */
    public function viewLocation($locationId, $viewType, $layout = false, array $params = [])
    {
        @trigger_error(
            "ViewController::viewLocation() is deprecated since kernel 6.0.0, and will be removed in the future.\n" .
            'Use ViewController::viewAction() instead.',
            E_USER_DEPRECATED
        );

        $this->performAccessChecks();
        $response = $this->buildResponse();

        try {
            if (isset($params['location']) && $params['location'] instanceof Location) {
                $location = $params['location'];
            } else {
                $location = $this->getRepository()->getLocationService()->loadLocation($locationId);
                if ($location->invisible) {
                    throw new NotFoundHttpException("Location #$locationId cannot be displayed as it is flagged as invisible.");
                }
            }

            $response->headers->set('X-Location-Id', $locationId);
            $response->setContent(
                $this->renderLocation(
                    $location,
                    $viewType,
                    $layout,
                    $params
                )
            );

            return $response;
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            return $this->handleViewException($response, $params, $e, $viewType, null, $locationId);
        }
    }

    /**
     * Main action for viewing embedded location.
     * Response will be cached with HttpCache validation model (Etag).
     *
     * @param int $locationId
     * @param string $viewType
     * @param bool $layout
     * @param array $params
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated Since 6.0.0. Viewing locations is now done with ViewContent.
     */
    public function embedLocation($locationId, $viewType, $layout = false, array $params = [])
    {
        @trigger_error(
            "ViewController::embedLocation() is deprecated since kernel 6.0.0, and will be removed in the future.\n" .
            'Use ViewController::viewAction() instead.',
            E_USER_DEPRECATED
        );

        $this->performAccessChecks();
        $response = $this->buildResponse();

        try {
            /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
            $location = $this->getRepository()->sudo(
                function (Repository $repository) use ($locationId) {
                    return $repository->getLocationService()->loadLocation($locationId);
                }
            );

            if ($location->invisible) {
                throw new NotFoundHttpException("Location #{$locationId} cannot be displayed as it is flagged as invisible.");
            }

            // Check both 'content/read' and 'content/view_embed'.
            if (
                !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute(
                        'content',
                        'read',
                        ['valueObject' => $location->contentInfo, 'targets' => $location]
                    )
                )
                && !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute(
                        'content',
                        'view_embed',
                        ['valueObject' => $location->contentInfo, 'targets' => $location]
                    )
                )
            ) {
                throw new AccessDeniedException();
            }

            if ($response->isNotModified($this->getRequest())) {
                return $response;
            }

            $response->headers->set('X-Location-Id', $locationId);
            $response->setContent(
                $this->renderLocation(
                    $location,
                    $viewType,
                    $layout,
                    $params
                )
            );

            return $response;
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (Exception $e) {
            return $this->handleViewException($response, $params, $e, $viewType, null, $locationId);
        }
    }

    /**
     * Main action for viewing content.
     * Response will be cached with HttpCache validation model (Etag).
     *
     * @param int $contentId
     * @param string $viewType
     * @param bool $layout
     * @param array $params
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated Since 6.0.0. Viewing content is now done with ViewAction.
     */
    public function viewContent($contentId, $viewType, $layout = false, array $params = [])
    {
        @trigger_error(
            "ViewController::viewContent() is deprecated since kernel 6.0.0, and will be removed in the future.\n" .
            'Use ViewController::viewAction() instead.',
            E_USER_DEPRECATED
        );

        if ($viewType === 'embed') {
            return $this->embedContent($contentId, $viewType, $layout, $params);
        }

        $this->performAccessChecks();
        $response = $this->buildResponse();

        try {
            $content = $this->getRepository()->getContentService()->loadContent($contentId);

            if ($response->isNotModified($this->getRequest())) {
                return $response;
            }

            if (!isset($params['location']) && !isset($params['locationId'])) {
                $params['location'] = $this->getRepository()->getLocationService()->loadLocation($content->contentInfo->mainLocationId);
            }
            $response->headers->set('X-Location-Id', $content->contentInfo->mainLocationId);
            $response->setContent(
                $this->renderContent($content, $viewType, $layout, $params)
            );

            return $response;
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (Exception $e) {
            return $this->handleViewException($response, $params, $e, $viewType, $contentId);
        }
    }

    /**
     * Main action for viewing embedded content.
     * Response will be cached with HttpCache validation model (Etag).
     *
     * @param int $contentId
     * @param string $viewType
     * @param bool $layout
     * @param array $params
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated Since 6.0.0. Embedding content is now done with EmbedAction.
     */
    public function embedContent($contentId, $viewType, $layout = false, array $params = [])
    {
        @trigger_error(
            "ViewController::embedContent() is deprecated since kernel 6.0.0, and will be removed in the future.\n" .
            'Use ViewController::viewAction() instead.',
            E_USER_DEPRECATED
        );

        $this->performAccessChecks();
        $response = $this->buildResponse();

        try {
            /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
            $content = $this->getRepository()->sudo(
                function (Repository $repository) use ($contentId) {
                    return $repository->getContentService()->loadContent($contentId);
                }
            );

            // Check both 'content/read' and 'content/view_embed'.
            if (
                !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute('content', 'read', ['valueObject' => $content])
                )
                && !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute('content', 'view_embed', ['valueObject' => $content])
                )
            ) {
                throw new AccessDeniedException();
            }

            // Check that Content is published, since sudo allows loading unpublished content.
            if (
                !$content->getVersionInfo()->isPublished()
                && !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute('content', 'versionread', ['valueObject' => $content])
                )
            ) {
                throw new AccessDeniedException();
            }

            if ($response->isNotModified($this->getRequest())) {
                return $response;
            }

            $response->setContent(
                $this->renderContent($content, $viewType, $layout, $params)
            );

            return $response;
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (Exception $e) {
            return $this->handleViewException($response, $params, $e, $viewType, $contentId);
        }
    }

    protected function handleViewException(Response $response, $params, Exception $e, $viewType, $contentId = null, $locationId = null)
    {
        $event = new APIContentExceptionEvent(
            $e,
            [
                'contentId' => $contentId,
                'locationId' => $locationId,
                'viewType' => $viewType,
            ]
        );
        $this->getEventDispatcher()->dispatch(MVCEvents::API_CONTENT_EXCEPTION, $event);
        if ($event->hasContentView()) {
            $response->setContent(
                $this->viewManager->renderContentView(
                    $event->getContentView(),
                    $params
                )
            );

            return $response;
        }

        throw $e;
    }

    /**
     * Creates the content to be returned when viewing a Location.
     *
     * @param Location $location
     * @param string $viewType
     * @param bool $layout
     * @param array $params
     *
     * @return string
     */
    protected function renderLocation(Location $location, $viewType, $layout = false, array $params = [])
    {
        return $this->viewManager->renderLocation($location, $viewType, $params + ['noLayout' => !$layout]);
    }

    /**
     * Creates the content to be returned when viewing a Content.
     *
     * @param Content $content
     * @param string $viewType
     * @param bool $layout
     * @param array $params
     *
     * @return string
     */
    protected function renderContent(Content $content, $viewType, $layout = false, array $params = [])
    {
        return $this->viewManager->renderContent($content, $viewType, $params + ['noLayout' => !$layout]);
    }

    /**
     * Performs the access checks.
     */
    protected function performAccessChecks()
    {
        if (!$this->isGranted(new AuthorizationAttribute('content', 'read'))) {
            throw new AccessDeniedException();
        }
    }
}
