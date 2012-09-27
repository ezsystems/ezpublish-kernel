<?php
/**
 * File containing the URLWildcard controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use \eZ\Publish\API\Repository\URLWildcardService;

use Qafoo\RMF;

/**
 * URLWildcard controller
 */
class URLWildcard
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * URLWildcard service
     *
     * @var \eZ\Publish\API\Repository\URLWildcardService
     */
    protected $urlWildcardService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\URLWildcardService $urlWildcardService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, URLWildcardService $urlWildcardService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler = $urlHandler;
        $this->urlWildcardService = $urlWildcardService;
    }

    /**
     * Returns the URL wildcard with the given id
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     */
    public function loadURLWildcard( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'urlWildcard', $request->path );
        return $this->urlWildcardService->load( $values['urlwildcard'] );
    }

    /**
     * Returns the list of URL wildcards
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\URLWildcardList
     */
    public function listURLWildcards( RMF\Request $request )
    {
        return new Values\URLWildcardList(
            $this->urlWildcardService->loadAll()
        );
    }

    /**
     * Creates a new URL wildcard
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedURLWildcard
     */
    public function createURLWildcard( RMF\Request $request )
    {
        $urlWildcardCreate = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteURLWildcard( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'urlWildcard', $request->path );
        $this->urlWildcardService->remove(
            $this->urlWildcardService->load( $values['urlwildcard'] )
        );

        return new Values\ResourceDeleted();
    }
}
