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
use eZ\Publish\Core\REST\Common\Values\RestObjectState;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\ContentService;

use eZ\Publish\Core\REST\Common\Values\ContentObjectStates;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;

use Qafoo\RMF;

/**
 * ObjectState controller
 */
class ObjectState extends RestController
{
    /**
     * ObjectState service
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $objectStateService;

    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\ObjectStateService $objectStateService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     */
    public function __construct( ObjectStateService $objectStateService, ContentService $contentService )
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Creates a new object state group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedObjectStateGroup
     */
    public function createObjectStateGroup( RMF\Request $request )
    {
        //@todo Error handling if identifier already exists
        //Problem being, PAPI does not specify exception in that case

        return new Values\CreatedObjectStateGroup(
            array(
                'objectStateGroup' => $this->objectStateService->createObjectStateGroup(
                    $this->inputDispatcher->parse(
                        new Message(
                            array( 'Content-Type' => $request->contentType ),
                            $request->body
                        )
                    )
                )
            )
        );
    }

    /**
     * Creates a new object state
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedObjectState
     */
    public function createObjectState( RMF\Request $request )
    {
        //@todo Error handling if identifier already exists
        //Problem being, PAPI does not specify exception in that case

        $values = $this->urlHandler->parse( 'objectstates', $request->path );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );

        return new Values\CreatedObjectState(
            array(
                'objectState' => new RestObjectState(
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
                )
            )
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
     * @return \eZ\Publish\Core\REST\Common\Values\RestObjectState
     */
    public function loadObjectState( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstate', $request->path );
        return new RestObjectState(
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
        $values = $this->urlHandler->parse( 'objectstates', $request->path );

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
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteObjectStateGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $request->path );
        $this->objectStateService->deleteObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * The given object state is deleted
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteObjectState( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectstate', $request->path );
        $this->objectStateService->deleteObjectState(
            $this->objectStateService->loadObjectState( $values['objectstate'] )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * Updates an object state group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup( RMF\Request $request )
    {
        //@todo Error handling if identifier already exists
        //Problem being, PAPI does not specify exception in that case

        $values = $this->urlHandler->parse( 'objectstategroup', $request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );
        return $this->objectStateService->updateObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] ),
            $updateStruct
        );
    }

    /**
     * Updates an object state
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Common\Values\RestObjectState
     */
    public function updateObjectState( RMF\Request $request )
    {
        //@todo Error handling if identifier already exists
        //Problem being, PAPI does not specify exception in that case

        $values = $this->urlHandler->parse( 'objectstate', $request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );
        return new RestObjectState(
            $this->objectStateService->updateObjectState(
                $this->objectStateService->loadObjectState( $values['objectstate'] ),
                $updateStruct
            ),
            $values['objectstategroup']
        );
    }

    /**
     * Returns the object states of content
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Common\Values\ContentObjectStates
     */
    public function getObjectStatesForContent( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectObjectStates', $request->path );
        $groups = $this->objectStateService->loadObjectStateGroups();
        $contentInfo = $this->contentService->loadContentInfo( $values['object'] );

        $contentObjectStates = array();

        foreach ( $groups as $group )
        {
            try
            {
                $state = $this->objectStateService->getObjectState( $contentInfo, $group );
                $contentObjectStates[] = new RestObjectState( $state, $group->id );
            }
            catch ( NotFoundException $e )
            {
                // Do nothing
            }
        }

        return new ContentObjectStates( $contentObjectStates );
    }

    /**
     * Updates object states of content
     * An object state in the input overrides the state of the object state group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Common\Values\ContentObjectStates
     */
    public function setObjectStatesForContent( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'objectObjectStates', $request->path );
        $newObjectStates = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

        $countByGroups = array();
        foreach ( $newObjectStates as $newObjectState )
        {
            $groupId = (int) $newObjectState->groupId;
            if ( array_key_exists( $groupId, $countByGroups ) )
            {
                $countByGroups[$groupId]++;
            }
            else
            {
                $countByGroups[$groupId] = 1;
            }
        }

        foreach ( $countByGroups as $groupId => $count )
        {
            if ( $count > 1 )
            {
                throw new ForbiddenException( "Multiple object states provided for group with ID $groupId" );
            }
        }

        $contentInfo = $this->contentService->loadContentInfo( $values['object'] );

        $contentObjectStates = array();
        foreach ( $newObjectStates as $newObjectState )
        {
            $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $newObjectState->groupId );
            $this->objectStateService->setObjectState( $contentInfo, $objectStateGroup, $newObjectState->objectState );
            $contentObjectStates[(int) $objectStateGroup->id] = $newObjectState;
        }

        return new ContentObjectStates( $contentObjectStates );
    }
}
