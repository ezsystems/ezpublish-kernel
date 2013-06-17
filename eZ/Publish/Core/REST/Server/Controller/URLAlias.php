<?php
/**
 * File containing the URLAlias controller class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\LocationService;

/**
 * URLAlias controller
 */
class URLAlias extends RestController
{
    /**
     * URLAlias service
     *
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( URLAliasService $urlAliasService, LocationService $locationService )
    {
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
    }

    /**
     * Returns the URL alias with the given ID
     *
     * @param $urlAliasId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    public function loadURLAlias( $urlAliasId )
    {
        return $this->urlAliasService->load( $urlAliasId );
    }

    /**
     * Returns the list of global URL aliases
     *
     * @return \eZ\Publish\Core\REST\Server\Values\URLAliasRefList
     */
    public function listGlobalURLAliases()
    {
        return new Values\URLAliasRefList(
            $this->urlAliasService->listGlobalAliases(),
            $this->requestParser->generate( 'urlAliases' )
        );
    }

    /**
     * Returns the list of URL aliases for a location
     *
     * @param $locationPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\URLAliasRefList
     */
    public function listLocationURLAliases( $locationPath )
    {
        $locationPathParts = explode( '/', $locationPath );

        $location = $this->locationService->loadLocation(
            array_pop( $locationPathParts )
        );

        $custom = isset( $this->request->variables['custom'] ) && $this->request->variables['custom'] === 'false' ? false : true;

        return new Values\URLAliasRefList(
            $this->urlAliasService->listLocationAliases( $location, $custom ),
            $this->request->path
        );
    }

    /**
     * Creates a new URL alias
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedURLAlias
     */
    public function createURLAlias()
    {
        $urlAliasCreate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        if ( $urlAliasCreate['_type'] === 'LOCATION' )
        {
            $locationUrlValues = $this->requestParser->parse( 'location', $urlAliasCreate['location']['_href'] );
            $locationPathParts = explode( '/', $locationUrlValues['location'] );

            $location = $this->locationService->loadLocation(
                array_pop( $locationPathParts )
            );

            try
            {
                $createdURLAlias = $this->urlAliasService->createUrlAlias(
                    $location,
                    $urlAliasCreate['path'],
                    $urlAliasCreate['languageCode'],
                    $urlAliasCreate['forward'],
                    $urlAliasCreate['alwaysAvailable']
                );
            }
            catch ( InvalidArgumentException $e )
            {
                throw new ForbiddenException( $e->getMessage() );
            }
        }
        else
        {
            try
            {
                $createdURLAlias = $this->urlAliasService->createGlobalUrlAlias(
                    $urlAliasCreate['resource'],
                    $urlAliasCreate['path'],
                    $urlAliasCreate['languageCode'],
                    $urlAliasCreate['forward'],
                    $urlAliasCreate['alwaysAvailable']
                );
            }
            catch ( InvalidArgumentException $e )
            {
                throw new ForbiddenException( $e->getMessage() );
            }
        }

        return new Values\CreatedURLAlias(
            array(
                'urlAlias' => $createdURLAlias
            )
        );
    }

    /**
     * The given URL alias is deleted
     *
     * @param $urlAliasId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteURLAlias( $urlAliasId )
    {
        $this->urlAliasService->removeAliases(
            array(
                $this->urlAliasService->load( $urlAliasId )
            )
        );

        return new Values\NoContent();
    }
}
