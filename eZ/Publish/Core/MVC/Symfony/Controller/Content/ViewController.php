<?php

/**
 * File containing the ViewController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DateTime;
use Exception;

class ViewController extends Controller
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ViewManagerInterface
     */
    protected $viewManager;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(ViewManagerInterface $viewManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->viewManager = $viewManager;
        $this->authorizationChecker = $authorizationChecker;
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
     */
    public function viewLocation($locationId, $viewType, $layout = false, array $params = array())
    {
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
     */
    public function embedLocation($locationId, $viewType, $layout = false, array $params = array())
    {
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
                        array('valueObject' => $location->contentInfo, 'targets' => $location)
                    )
                )
                && !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute(
                        'content',
                        'view_embed',
                        array('valueObject' => $location->contentInfo, 'targets' => $location)
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
     */
    public function viewContent($contentId, $viewType, $layout = false, array $params = array())
    {
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
     */
    public function embedContent($contentId, $viewType, $layout = false, array $params = array())
    {
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
                    new AuthorizationAttribute('content', 'read', array('valueObject' => $content))
                )
                && !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute('content', 'view_embed', array('valueObject' => $content))
                )
            ) {
                throw new AccessDeniedException();
            }

            // Check that Content is published, since sudo allows loading unpublished content.
            if (
                $content->getVersionInfo()->status !== APIVersionInfo::STATUS_PUBLISHED
                && !$this->authorizationChecker->isGranted(
                    new AuthorizationAttribute('content', 'versionread', array('valueObject' => $content))
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
            array(
                'contentId' => $contentId,
                'locationId' => $locationId,
                'viewType' => $viewType,
            )
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
    protected function renderLocation(Location $location, $viewType, $layout = false, array $params = array())
    {
        return $this->viewManager->renderLocation($location, $viewType, $params + array('noLayout' => !$layout));
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
    protected function renderContent(Content $content, $viewType, $layout = false, array $params = array())
    {
        return $this->viewManager->renderContent($content, $viewType, $params + array('noLayout' => !$layout));
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
