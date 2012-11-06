<?php
/**
 * File containing the ObjectState controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Common\Values\RestObjectState;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\ContentService;

use eZ\Publish\Core\REST\Common\Values\ContentObjectStates;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\REST\Server\Exceptions\ForbiddenException;

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
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedObjectStateGroup
     */
    public function createObjectStateGroup()
    {
        try
        {
            $createdStateGroup = $this->objectStateService->createObjectStateGroup(
                $this->inputDispatcher->parse(
                    new Message(
                        array( 'Content-Type' => $this->request->contentType ),
                        $this->request->body
                    )
                )
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\CreatedObjectStateGroup(
            array(
                'objectStateGroup' => $createdStateGroup
            )
        );
    }

    /**
     * Creates a new object state
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedObjectState
     */
    public function createObjectState()
    {
        $values = $this->urlHandler->parse( 'objectstates', $this->request->path );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );

        try
        {
            $createdObjectState = $this->objectStateService->createObjectState(
                $objectStateGroup,
                $this->inputDispatcher->parse(
                    new Message(
                        array( 'Content-Type' => $this->request->contentType ),
                        $this->request->body
                    )
                )
            );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }

        return new Values\CreatedObjectState(
            array(
                'objectState' => new RestObjectState(
                    $createdObjectState,
                    $objectStateGroup->id
                )
            )
        );
    }

    /**
     * Loads an object state group
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function loadObjectStateGroup()
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $this->request->path );
        return $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );
    }

    /**
     * Loads an object state
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestObjectState
     */
    public function loadObjectState()
    {
        $values = $this->urlHandler->parse( 'objectstate', $this->request->path );
        return new RestObjectState(
            $this->objectStateService->loadObjectState( $values['objectstate'] ),
            $values['objectstategroup']
        );
    }

    /**
     * Returns a list of all object state groups
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ObjectStateGroupList
     */
    public function loadObjectStateGroups()
    {
        return new Values\ObjectStateGroupList(
            $this->objectStateService->loadObjectStateGroups()
        );
    }

    /**
     * Returns a list of all object states of the given group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ObjectStateList
     */
    public function loadObjectStates()
    {
        $values = $this->urlHandler->parse( 'objectstates', $this->request->path );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );
        return new Values\ObjectStateList(
            $this->objectStateService->loadObjectStates( $objectStateGroup ),
            $objectStateGroup->id
        );
    }

    /**
     * The given object state group including the object states is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteObjectStateGroup()
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $this->request->path );
        $this->objectStateService->deleteObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] )
        );

        return new Values\NoContent();
    }

    /**
     * The given object state is deleted
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteObjectState()
    {
        $values = $this->urlHandler->parse( 'objectstate', $this->request->path );
        $this->objectStateService->deleteObjectState(
            $this->objectStateService->loadObjectState( $values['objectstate'] )
        );

        return new Values\NoContent();
    }

    /**
     * Updates an object state group
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup()
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $this->request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup( $values['objectstategroup'] );

        try
        {
            $updatedStateGroup = $this->objectStateService->updateObjectStateGroup( $objectStateGroup, $updateStruct );
            return $updatedStateGroup;
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }
    }

    /**
     * Updates an object state
     *
     * @return \eZ\Publish\Core\REST\Common\Values\RestObjectState
     */
    public function updateObjectState()
    {
        $values = $this->urlHandler->parse( 'objectstate', $this->request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $objectState = $this->objectStateService->loadObjectState( $values['objectstate'] );

        try
        {
            $updatedObjectState = $this->objectStateService->updateObjectState( $objectState, $updateStruct );
            return new RestObjectState( $updatedObjectState, $values['objectstategroup'] );
        }
        catch ( InvalidArgumentException $e )
        {
            throw new ForbiddenException( $e->getMessage() );
        }
    }

    /**
     * Returns the object states of content
     *
     * @return \eZ\Publish\Core\REST\Common\Values\ContentObjectStates
     */
    public function getObjectStatesForContent()
    {
        $values = $this->urlHandler->parse( 'objectObjectStates', $this->request->path );
        $groups = $this->objectStateService->loadObjectStateGroups();
        $contentInfo = $this->contentService->loadContentInfo( $values['object'] );

        $contentObjectStates = array();

        foreach ( $groups as $group )
        {
            try
            {
                $state = $this->objectStateService->getContentState( $contentInfo, $group );
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
     * @return \eZ\Publish\Core\REST\Common\Values\ContentObjectStates
     */
    public function setObjectStatesForContent()
    {
        $values = $this->urlHandler->parse( 'objectObjectStates', $this->request->path );
        $newObjectStates = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
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
            $this->objectStateService->setContentState( $contentInfo, $objectStateGroup, $newObjectState->objectState );
            $contentObjectStates[(int) $objectStateGroup->id] = $newObjectState;
        }

        return new ContentObjectStates( $contentObjectStates );
    }
}
