<?php
/**
 * File containing the ObjectState controller class
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

use eZ\Publish\API\Repository\ObjectStateService;

use Qafoo\RMF;

/**
 * ObjectState controller
 */
class ObjectState
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
     * ObjectState service
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\ObjectStateService $objectStateService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, ObjectStateService $objectStateService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler = $urlHandler;
        $this->objectStateService = $objectStateService;
    }

    /**
     * Creates a new object state group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function createObjectStateGroup( RMF\Request $request )
    {
        return $this->objectStateService->createObjectStateGroup(
            $this->inputDispatcher->parse(
                new Message(
                    array( 'Content-Type' => $request->contentType ),
                    $request->body
                )
            )
        );
    }

    /**
     * Creates a new object state
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function createObjectState( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstates', $request->path );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );

        return new Values\ObjectState(
            $this->objectStateService->createObjectState(
                $objectStateGroup,
                $this->inputDispatcher->parse(
                    new Message(
                        array( 'Content-Type' => $request->contentType ),
                        $request->body
                    )
                )
            ),
            $objectStateGroup->id
        );
    }

    /**
     * Loads an object state group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function loadObjectStateGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $request->path );
        return $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );
    }

    /**
     * Loads an object state
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ObjectState
     */
    public function loadObjectState( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstate', $request->path );
        return new Values\ObjectState(
            $this->objectStateService->loadObjectState( $values['objectstate'] ),
            $values['objectstategroup']
        );
    }

    /**
     * Returns a list of all object state groups
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ObjectStateGroupList
     */
    public function loadObjectStateGroups( RMF\Request $request )
    {
        return new Values\ObjectStateGroupList(
            $this->objectStateService->loadObjectStateGroups()
        );
    }

    /**
     * Returns a list of all object states of the given group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ObjectStateList
     */
    public function loadObjectStates( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstate', $request->path );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );
        return new Values\ObjectStateList(
            $this->objectStateService->loadObjectStates( $objectStateGroup ),
            $objectStateGroup->id
        );
    }

    /**
     * The given object state group including the object states is deleted
     *
     * @param RMF\Request $request
     * @return void
     */
    public function deleteObjectStateGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $request->path );
        return $this->objectStateService->deleteObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] )
        );
    }

    /**
     * The given object state is deleted
     *
     * @param RMF\Request $request
     * @return void
     */
    public function deleteObjectState( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstate', $request->path );
        return $this->objectStateService->deleteObjectState(
            $this->objectStateService->loadObjectState( $values['objectstate'] )
        );
    }

    /**
     * Updates an object state
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ObjectState
     */
    public function updateObjectState( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstate', $request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );
        return new Values\ObjectState(
            $this->objectStateService->updateObjectState(
                $this->objectStateService->loadObjectState( $values['objectstate'] ),
                $updateStruct
            ),
            $values['objectstategroup']
        );
    }
}
