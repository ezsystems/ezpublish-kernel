<?php
/**
 * File containing the Trash controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\TrashService;

use eZ\Publish\API\Repository\Values\Content\Query;

use Qafoo\RMF;

/**
 * Trash controller
 */
class Trash
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
     * Trash service
     *
     * @var \eZ\Publish\API\Repository\TrashService
     */
    protected $trashService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\TrashService $trashService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, TrashService $trashService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler = $urlHandler;
        $this->trashService = $trashService;
    }

    /**
     * Returns a list of all trash items
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\LocationList
     */
    public function loadTrashItems( RMF\Request $request )
    {
        return new Values\LocationList(
            $this->trashService->findTrashItems(
                new Query()
            )->items,
            $request->path
        );
    }

    /**
     * Returns the trash item given by id
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadTrashItem( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'trash', $request->path );
        $this->trashService->loadTrashItem( $values['trash'] );
    }
}
