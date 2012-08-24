<?php
/**
 * File containing the ViewController class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\Controller\Content;

use eZ\Publish\MVC\Controller\Controller,
    eZ\Publish\MVC\View\Manager as ViewManager,
    eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\APIContentExceptionEvent,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ViewController extends Controller
{
    /**
     * @var \eZ\Publish\MVC\View\Manager
     */
    private $viewManager;

    public function __construct( ViewManager $viewManager, array $options = array() )
    {
        $this->viewManager = $viewManager;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions( $resolver );
        parent::__construct( $resolver->resolve( $options ) );
    }

    protected function setDefaultOptions( OptionsResolverInterface $resolver )
    {
        $resolver->setDefaults(
            array(
                 'viewCache'    => true,
                 'defaultTTL'   => 60,
                 'TTLCache'     => false
            )
        );
    }

    /**
     * Main action for viewing content through a location in the repository.
     * Response will be cached with HttpCache validation model (Etag)
     *
     * @param int $locationId
     * @param string $viewType
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function viewLocation( $locationId, $viewType )
    {
        $response = new Response();
        $request = $this->getRequest();
        $repository = $this->getRepository();
        // TODO: Use a dedicated etag generator, generating a hash instead of plain text
        $etag = "ezpublish-location-$locationId-$viewType";

        try
        {
            // Assume that location is cached by the repository
            $location = $repository->getLocationService()->loadLocation( $locationId );

            if ( $this->getOption( 'viewCache' ) === true )
            {
                $response->setPublic();
                $response->setEtag( $etag );

                // If-None-Match is the request counterpart of Etag response header
                // Making the response to vary against it ensures that an HTTP reverse proxy caches the different possible variations of the response
                // as it can depend on user role for instance.
                if ( $request->headers->has( 'If-None-Match' ) && $this->getOption( 'TTLCache' ) === true )
                {
                    $response->setVary( 'If-None-Match' );
                    $response->setMaxAge( $this->getOption( 'defaultTTL' ) );
                }

                $response->setLastModified( $location->getContentInfo()->modificationDate );
                if ( $response->isNotModified( $this->getRequest() ) )
                {
                    return $response;
                }
            }

            $response->setContent(
                $this->viewManager->renderLocation(
                    $location,
                    $repository
                        ->getContentService()
                        ->loadContentByContentInfo( $location->getContentInfo() )
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
}
