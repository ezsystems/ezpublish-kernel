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
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\MVC\View\Manager as ViewManager,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class ViewController extends Controller
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\MVC\View\Manager
     */
    private $viewManager;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct( Repository $repository, ViewManager $viewManager, Request $request )
    {
        $this->repository = $repository;
        $this->viewManager = $viewManager;
        $this->request = $request;
    }

    /**
     * Main action for viewing content through a location in the repository.
     * Response will be cached with HttpCache validation model (Etag)
     *
     * @param int $locationId
     * @param string $viewMode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewLocation( $locationId, $viewMode )
    {
        // Assume that location is cached by the repository
        $location = $this->repository->getLocationService()->loadLocation( $locationId );

        $response = new Response();
        $response->setPublic();
        // TODO: Use a dedicated etag generator, generating a hash instead of plain text
        $response->setEtag( "ezpublish-location-$locationId-$viewMode" );
        $response->setLastModified( $location->getContentInfo()->modificationDate );
        if ( $response->isNotModified( $this->request ) )
        {
            return $response;
        }

        $response->setContent(
            $this->viewManager->renderLocation(
                $location,
                $this
                    ->repository
                    ->getContentService()
                    ->loadContentByContentInfo( $location->getContentInfo() )
            )
        );

        return $response;
    }
}
