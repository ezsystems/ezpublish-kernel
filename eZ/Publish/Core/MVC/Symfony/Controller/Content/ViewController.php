<?php
/**
 * File containing the ViewController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\APIContentExceptionEvent;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DateTime;
use Exception;

class ViewController extends Controller
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    protected $viewManager;

    public function __construct( ViewManager $viewManager )
    {
        $this->viewManager = $viewManager;
    }

    /**
     * Build the response so that depending on settings it's cacheable
     *
     * @param string|null $etag
     * @param \DateTime|null $lastModified
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse( $etag = null, DateTime $lastModified = null )
    {
        $request = $this->getRequest();
        $response = new Response();
        if ( $this->getParameter( 'content.view_cache' ) === true )
        {
            $response->setPublic();
            if ( $etag !== null )
            {
                $response->setEtag( $etag );
            }

            // Make the response vary against X-User-Hash header ensures that an HTTP
            // reverse proxy caches the different possible variations of the
            // response as it can depend on user role for instance.
            if (
                $request->headers->has( 'X-User-Hash' )
                && $this->getParameter( 'content.ttl_cache' ) === true
            )
            {
                $response->setVary( 'X-User-Hash' );
                $response->setSharedMaxAge(
                    $this->getParameter( 'content.default_ttl' )
                );
            }

            if ( $lastModified != null )
            {
                $response->setLastModified( $lastModified );
            }
        }
        return $response;
    }

    /**
     * Main action for viewing content through a location in the repository.
     * Response will be cached with HttpCache validation model (Etag)
     *
     * @param int $locationId
     * @param string $viewType
     * @param boolean $layout
     * @param array $params
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewLocation( $locationId, $viewType, $layout = false, array $params = array() )
    {
        $this->performAccessChecks();
        $response = $this->buildResponse();

        try
        {
            if ( isset( $params['location'] ) && $params['location'] instanceof Location )
            {
                $location = $params['location'];
            }
            else
            {
                $location = $this->getRepository()->getLocationService()->loadLocation( $locationId );
            }

            $response->headers->set( 'X-Location-Id', $locationId );
            $response->setContent(
                $this->renderLocation(
                    $location,
                    $viewType,
                    $layout,
                    $params
                )
            );

            return $response;
        }
        catch ( UnauthorizedException $e )
        {
            throw new AccessDeniedException();
        }
        catch ( Exception $e )
        {
            return $this->handleViewException( $response, $params, $e, $viewType, null, $locationId );
        }
    }

    /**
     * Main action for viewing content.
     * Response will be cached with HttpCache validation model (Etag)
     *
     * @param int $contentId
     * @param string $viewType
     * @param boolean $layout
     * @param array $params
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewContent( $contentId, $viewType, $layout = false, array $params = array() )
    {
        $this->performAccessChecks();
        $response = $this->buildResponse();

        try
        {
            switch ( $viewType )
            {
                // If a 'view_embed' permission exists, do not rely on the repository's 'content/read' check.
                case 'embed':
                    {
                        $content = $this->getRepository()->sudo(
                            function ( $repository ) use ( $contentId )
                            {
                                return $repository->getContentService()->loadContent( $contentId );
                            }
                        );

                        if (
                            !$this->getRepository()->canUser( 'content', 'read', $content )
                            && !$this->getRepository()->canUser( 'content', 'view_embed', $content )
                        )
                            throw new UnauthorizedException( 'content', 'read' );

                        // also check of content publish status, since sudo allows loading unpublished content.
                        if (
                            $content->getVersionInfo()->status !== APIVersionInfo::STATUS_PUBLISHED
                            && !$this->getRepository()->canUser( 'content', 'versionread', $content )
                        )
                            throw new UnauthorizedException( 'content', 'versionread' );
                        break;
                    }
                default:
                    {
                        $content = $this->getRepository()->getContentService()->loadContent( $contentId );
                        break;
                    }
            }

            if ( $response->isNotModified( $this->getRequest() ) )
            {
                return $response;
            }

            $response->setContent(
                $this->renderContent( $content, $viewType, $layout, $params )
            );

            return $response;
        }
        catch ( UnauthorizedException $e )
        {
            throw new AccessDeniedException();
        }
        catch ( Exception $e )
        {
            return $this->handleViewException( $response, $params, $e, $viewType, $contentId );
        }
    }

    protected function handleViewException( Response $response, $params, Exception $e, $viewType, $contentId = null, $locationId = null )
    {
        $event = new APIContentExceptionEvent(
            $e,
            array(
                'contentId'    => $contentId,
                'locationId'   => $locationId,
                'viewType'     => $viewType
            )
        );
        $this->getEventDispatcher()->dispatch( MVCEvents::API_CONTENT_EXCEPTION, $event );
        if ( $event->hasContentView() )
        {
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
     * Creates the content to be returned when viewing a Location
     *
     * @param Location $location
     * @param string $viewType
     * @param boolean $layout
     * @param array $params
     */
    protected function renderLocation( $location, $viewType, $layout = false, array $params = array() )
    {
        return $this->viewManager->renderLocation( $location, $viewType, $params + array( 'noLayout' => !$layout ) );
    }

    /**
     * Creates the content to be returned when viewing a Content
     *
     * @param Content $content
     * @param string $viewType
     * @param boolean $layout
     * @param array $params
     */
    protected function renderContent( $content, $viewType, $layout = false, array $params = array() )
    {
        return $this->viewManager->renderContent( $content, $viewType, $params + array( 'noLayout' => !$layout ) );
    }

    /**
     * Performs the access checks
     */
    protected function performAccessChecks()
    {
        if ( !$this->isGranted( new AuthorizationAttribute( 'content', 'read' ) ) )
            throw new AccessDeniedException();
    }
}
