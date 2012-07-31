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
                 'viewCache'    => true
            )
        );
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
        $repository = $this->getRepository();
        $location = $repository->getLocationService()->loadLocation( $locationId );

        $response = new Response();
        if ( $this->getOption( 'viewCache' ) === true )
        {
            $response->setPublic();
            // TODO: Use a dedicated etag generator, generating a hash instead of plain text
            $response->setEtag( "ezpublish-location-$locationId-$viewMode" );
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
}
