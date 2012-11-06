<?php
/**
 * File containing the URLWildcard controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use \eZ\Publish\API\Repository\URLWildcardService;

/**
 * URLWildcard controller
 */
class URLWildcard extends RestController
{
    /**
     * URLWildcard service
     *
     * @var \eZ\Publish\API\Repository\URLWildcardService
     */
    protected $urlWildcardService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\URLWildcardService $urlWildcardService
     */
    public function __construct( URLWildcardService $urlWildcardService )
    {
        $this->urlWildcardService = $urlWildcardService;
    }

    /**
     * Returns the URL wildcard with the given id
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     */
    public function loadURLWildcard()
    {
        $values = $this->urlHandler->parse( 'urlWildcard', $this->request->path );
        return $this->urlWildcardService->load( $values['urlwildcard'] );
    }

    /**
     * Returns the list of URL wildcards
     *
     * @return \eZ\Publish\Core\REST\Server\Values\URLWildcardList
     */
    public function listURLWildcards()
    {
        return new Values\URLWildcardList(
            $this->urlWildcardService->loadAll()
        );
    }

    /**
     * Creates a new URL wildcard
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedURLWildcard
     */
    public function createURLWildcard()
    {
        $urlWildcardCreate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        try
        {
            $createdURLWildcard = $this->urlWildcardService->create(
                $urlWildcardCreate['sourceUrl'],
                $urlWildcardCreate['destinationUrl'],
                $urlWildcardCreate['forward']
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\CreatedURLWildcard(
            array(
                'urlWildcard' => $createdURLWildcard
            )
        );
    }

    /**
     * The given URL wildcard is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteURLWildcard()
    {
        $values = $this->urlHandler->parse( 'urlWildcard', $this->request->path );
        $this->urlWildcardService->remove(
            $this->urlWildcardService->load( $values['urlwildcard'] )
        );

        return new Values\NoContent();
    }
}
