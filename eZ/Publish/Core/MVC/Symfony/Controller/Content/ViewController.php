<?php
/**
 * File containing the ViewController class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\APIContentExceptionEvent;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use DateTime;

class ViewController extends Controller
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Manager
     */
    private $viewManager;

    public function __construct( ViewManager $viewManager )
    {
        $this->viewManager = $viewManager;
    }

    /**
     * Build the response so that depending on settings it's cacheable
     *
     * @param string $etag
     * @param \DateTime $lastModified
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse( $etag, DateTime $lastModified )
    {
        $request = $this->getRequest();
        $response = new Response();
        if ( $this->getParameter( 'content.view_cache' ) === true )
        {
            $response->setPublic();
            $response->setEtag( $etag );

            // If-None-Match is the request counterpart of Etag response header
            // Making the response to vary against it ensures that an HTTP
            // reverse proxy caches the different possible variations of the
            // response as it can depend on user role for instance.
            if ( $request->headers->has( 'If-None-Match' )
                && $this->getParameter( 'content.ttl_cache' ) === true )
            {
                $response->setVary( 'If-None-Match' );
                $response->setSharedMaxAge(
                    $this->getParameter( 'content.default_ttl' )
                );
            }

            $response->setLastModified( $lastModified );
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
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewLocation( $locationId, $viewType, $layout = false )
    {
        if ( !$this->isGranted( new AuthorizationAttribute( 'content', 'read' ) ) )
            throw new AccessDeniedException();

        try
        {
            // Assume that location is cached by the repository
            $location = $this->getRepository()->getLocationService()->loadLocation( $locationId );
            $contentInfo = $location->getContentInfo();

            // @todo: Use a dedicated etag generator, generating a hash
            // instead of plain text
            $response = $this->buildResponse(
                "ezpublish-location-$locationId-$contentInfo->currentVersionNo-$viewType-$layout",
                $contentInfo->modificationDate
            );
            $response->headers->set( 'X-Location-Id', $locationId );

            if ( $response->isNotModified( $this->getRequest() ) )
            {
                return $response;
            }

            $response->setContent(
                $this->viewManager->renderLocation(
                    $location,
                    $viewType,
                    array( 'noLayout' => !$layout )
                )
            );

            return $response;
        }
        catch ( \Exception $e )
        {
            $event = new APIContentExceptionEvent(
                $e,
                array(
                    'contentId'    => null,
                    'locationId'   => $locationId,
                    'viewType'     => $viewType
                )
            );
            $this->getEventDispatcher()->dispatch( MVCEvents::API_CONTENT_EXCEPTION, $event );
            if ( $event->hasContentView() )
            {
                $response->setContent(
                    $this->viewManager->renderContentView(
                        $event->getContentView()
                    )
                );

                return $response;
            }

            throw $e;
        }
    }

    /**
     * Main action for viewing content.
     * Response will be cached with HttpCache validation model (Etag)
     *
     * @param int $contentId
     * @param string $viewType
     * @param boolean $layout
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewContent( $contentId, $viewType, $layout = false )
    {
        if ( !$this->isGranted( new AuthorizationAttribute( 'content', 'read' ) ) )
            throw new AccessDeniedException();

        try
        {
            $content = $this->getRepository()->getContentService()->loadContent( $contentId );

            // @todo: Use a dedicated etag generator, generating a hash
            // instead of plain text
            $response = $this->buildResponse(
                "ezpublish-content-$contentId-$viewType-$layout",
                $content->contentInfo->modificationDate
            );

            if ( $response->isNotModified( $this->getRequest() ) )
            {
                return $response;
            }

            $response->setContent(
                $this->viewManager->renderContent(
                    $content,
                    $viewType,
                    array( 'noLayout' => !$layout )
                )
            );

            return $response;
        }
        catch ( \Exception $e )
        {
            $event = new APIContentExceptionEvent(
                $e,
                array(
                    'contentId'    => $contentId,
                    'locationId'   => null,
                    'viewType'     => $viewType
                )
            );
            $this->getEventDispatcher()->dispatch( MVCEvents::API_CONTENT_EXCEPTION, $event );
            if ( $event->hasContentView() )
            {
                $response->setContent(
                    $this->viewManager->renderContentView(
                        $event->getContentView()
                    )
                );

                return $response;
            }

            throw $e;
        }
    }
}
