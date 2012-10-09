<?php
/**
 * File containing the ViewController class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\Core\MVC\Symfony\Controller\Controller,
    eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager,
    eZ\Publish\Core\MVC\Symfony\MVCEvents,
    eZ\Publish\Core\MVC\Symfony\Event\APIContentExceptionEvent,
    eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    protected function buildResponse( $etag, $lastModified )
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
                $response->setMaxAge(
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
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewLocation( $locationId, $viewType )
    {
        if ( !$this->isGranted( new AuthorizationAttribute( 'content', 'read' ) ) )
            throw new AccessDeniedException();

        try
        {
            // Assume that location is cached by the repository
            $location = $this->getRepository()->getLocationService()->loadLocation( $locationId );

            // TODO: Use a dedicated etag generator, generating a hash
            // instead of plain text
            $response = $this->buildResponse(
                "ezpublish-location-$locationId-$viewType",
                $location->getContentInfo()->modificationDate
            );


            if ( $this->getParameter( 'content.view_cache' ) === true
                && $response->isNotModified( $this->getRequest() ) )
            {
                return $response;
            }

            $response->setContent(
                $this->viewManager->renderLocation( $location, $viewType )
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
     * @param boolean $noLayout
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewContent( $contentId, $viewType, $noLayout )
    {
        if ( !$this->isGranted( new AuthorizationAttribute( 'content', 'read' ) ) )
            throw new AccessDeniedException();

        try
        {
            $content = $this->getRepository()->getContentService()->loadContent( $contentId );

            // TODO: Use a dedicated etag generator, generating a hash
            // instead of plain text
            $response = $this->buildResponse(
                "ezpublish-content-$contentId-$viewType-$noLayout",
                $content->contentInfo->modificationDate
            );

            if ( $this->getParameter( 'content.view_cache' ) === true
                && $response->isNotModified( $this->getRequest() ) )
            {
                return $response;
            }

            $response->setContent(
                $this->viewManager->renderContent(
                    $content,
                    $viewType,
                    array( 'noLayout' => $noLayout )
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
