<?php
/**
 * File containing the ObjectStateService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\REST\Client
 */

namespace eZ\Publish\Core\REST\Client;

use \eZ\Publish\Core\REST\Common\UrlHandler;
use \eZ\Publish\Core\REST\Common\Input;
use \eZ\Publish\Core\REST\Common\Output;
use \eZ\Publish\Core\REST\Common\Message;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\Core\REST\Common\Values\ContentObjectStates;

/**
 * ObjectStateService service
 */
class ObjectStateService implements \eZ\Publish\API\Repository\ObjectStateService, Sessionable
{
    /**
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    private $inputDispatcher;

    /**
     * @var \eZ\Publish\Core\REST\Common\Output\Visitor
     */
    private $outputVisitor;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    private $urlHandler;

    /**
     * @param \eZ\Publish\Core\REST\Client\HttpClient $client
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $outputVisitor
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     */
    public function __construct( HttpClient $client, Input\Dispatcher $inputDispatcher, Output\Visitor $outputVisitor, UrlHandler $urlHandler )
    {
        $this->client          = $client;
        $this->inputDispatcher = $inputDispatcher;
        $this->outputVisitor   = $outputVisitor;
        $this->urlHandler      = $urlHandler;
    }

    /**
     * Set session ID
     *
     * Only for testing
     *
     * @param mixed $id
     * @private
     */
    public function setSession( $id )
    {
        if ( $this->outputVisitor instanceof Sessionable )
        {
            $this->outputVisitor->setSession( $id );
        }
    }

    /**
     * Creates a new object state group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an object state group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state group with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct $objectStateGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function createObjectStateGroup( ObjectStateGroupCreateStruct $objectStateGroupCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $objectStateGroupCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ObjectStateGroup' );

        $result = $this->client->request(
            'POST',
            $this->urlHandler->generate( 'objectstategroups' ),
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    /**
     * Loads a object state group
     *
     * @param mixed $objectStateGroupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the group was not found
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function loadObjectStateGroup( $objectStateGroupId )
    {
        $response = $this->client->request(
            'GET',
            $objectStateGroupId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ObjectStateGroup' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Loads all object state groups
     *
     * @param int $offset
     * @param int $limit
     * @todo Implement offset & limit
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function loadObjectStateGroups( $offset = 0, $limit = -1 )
    {
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'objectstategroups' ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ObjectStateGroupList' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * This method returns the ordered list of object states of a group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function loadObjectStates( ObjectStateGroup $objectStateGroup )
    {
        $values = $this->urlHandler->parse( 'objectstategroup', $objectStateGroup->id );
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'objectstates', array( 'objectstategroup' => $values['objectstategroup'] ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ObjectStateList' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Updates an object state group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state group with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup( ObjectStateGroup $objectStateGroup, ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $objectStateGroupUpdateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ObjectStateGroup' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        // Should originally be PATCH, but PHP's shiny new internal web server
        // dies with it.
        $result = $this->client->request(
            'POST',
            $objectStateGroup->id,
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    /**
     * Deletes a object state group including all states and links to content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     */
    public function deleteObjectStateGroup( ObjectStateGroup $objectStateGroup )
    {
        $response = $this->client->request(
            'DELETE',
            $objectStateGroup->id,
            new Message(
                // TODO: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "ObjectStateGroup" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ObjectStateGroup' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    /**
     * Creates a new object state in the given group.
     *
     * Note: in current kernel: If it is the first state all content objects will
     * set to this state.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an object state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state with provided identifier already exists in the same group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct $objectStateCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function createObjectState( ObjectStateGroup $objectStateGroup, ObjectStateCreateStruct $objectStateCreateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $objectStateCreateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ObjectState' );

        $result = $this->client->request(
            'POST',
            $this->urlHandler->generate( 'objectstates', array( 'objectstategroup' => $objectStateGroup->id ) ),
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    /**
     * Loads an object state
     *
     * @param $stateId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the state was not found
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function loadObjectState( $stateId )
    {
        $response = $this->client->request(
            'GET',
            $stateId,
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ObjectState' ) )
            )
        );
        return $this->inputDispatcher->parse( $response );
    }

    /**
     * Updates an object state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state with provided identifier already exists in the same group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function updateObjectState( ObjectState $objectState, ObjectStateUpdateStruct $objectStateUpdateStruct )
    {
        $inputMessage = $this->outputVisitor->visit( $objectStateUpdateStruct );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ObjectState' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        // Should originally be PATCH, but PHP's shiny new internal web server
        // dies with it.
        $result = $this->client->request(
            'POST',
            $objectState->id,
            $inputMessage
        );

        return $this->inputDispatcher->parse( $result );
    }

    /**
     * Changes the priority of the state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change priority on an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param int $priority
     */
    public function setPriorityOfObjectState( ObjectState $objectState, $priority )
    {
        throw new \Exception( "@todo Implement" );
    }

    /**
     * Deletes a object state. The state of the content objects is reset to the
     * first object state in the group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function deleteObjectState( ObjectState $objectState )
    {
        $response = $this->client->request(
            'DELETE',
            $objectState->id,
            new Message(
                // TODO: What media-type should we set here? Actually, it should be
                // all expected exceptions + none? Or is "ObjectState" correct,
                // since this is what is to be expected by the resource
                // identified by the URL?
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ObjectState' ) )
            )
        );

        if ( !empty( $response->body ) )
            $this->inputDispatcher->parse( $response );
    }

    /**
     * Sets the object-state of a state group to $state for the given content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state does not belong to the given group
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change the object state
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function setContentState( ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup, ObjectState $objectState )
    {
        $inputMessage = $this->outputVisitor->visit( new ContentObjectStates( array( $objectState ) ) );
        $inputMessage->headers['Accept'] = $this->outputVisitor->getMediaType( 'ContentObjectStates' );
        $inputMessage->headers['X-HTTP-Method-Override'] = 'PATCH';

        // Should originally be PATCH, but PHP's shiny new internal web server
        // dies with it.
        $values = $this->urlHandler->parse( 'object', $contentInfo->id );
        $result = $this->client->request(
            'POST',
            $this->urlHandler->generate( 'objectObjectStates', array( 'object' => $values['object'] ) ),
            $inputMessage
        );

        $this->inputDispatcher->parse( $result );
    }

    /**
     * Gets the object-state of object identified by $contentId.
     *
     * The $state is the id of the state within one group.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function getContentState( ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup )
    {
        $values = $this->urlHandler->parse( 'object', $contentInfo->id );
        $groupValues = $this->urlHandler->parse( 'objectstategroup', $objectStateGroup->id );
        $response = $this->client->request(
            'GET',
            $this->urlHandler->generate( 'objectObjectStates', array( 'object' => $values['object'] ) ),
            new Message(
                array( 'Accept' => $this->outputVisitor->getMediaType( 'ContentObjectStates' ) )
            )
        );

        $objectStates = $this->inputDispatcher->parse( $response );
        foreach ( $objectStates as $state )
        {
            $stateValues = $this->urlHandler->parse( 'objectstate', $state->id );
            if ( $stateValues['objectstategroup'] == $groupValues['objectstategroup'] )
            {
                return $state;
            }
        }
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return int
     */
    public function getContentCount( ObjectState $objectState )
    {
        throw new \Exception( "@todo Implement" );
    }

    /**
     * Instantiates a new Object State Group Create Struct and sets $identified in it.
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct
     */
    public function newObjectStateGroupCreateStruct( $identifier )
    {
        return new ObjectStateGroupCreateStruct(
            array(
                'identifier' => $identifier
            )
        );
    }

    /**
     * Instantiates a new Object State Group Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    public function newObjectStateGroupUpdateStruct()
    {
        return new ObjectStateGroupUpdateStruct();
    }

    /**
     * Instantiates a new Object State Create Struct and sets $identifier in it.
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct
     */
    public function newObjectStateCreateStruct( $identifier )
    {
        return new ObjectStateCreateStruct(
            array(
                'identifier' => $identifier
            )
        );
    }

    /**
     * Instantiates a new Object State Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct
     */
    public function newObjectStateUpdateStruct()
    {
        return new ObjectStateUpdateStruct();
    }
}
