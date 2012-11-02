<?php
/**
 * File containing the eZ\Publish\API\Repository\ObjectStateService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Content\ObjectState\Handler,

    eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct,
    eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct,
    eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct,
    eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct,
    eZ\Publish\API\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\ObjectState\ObjectState as APIObjectState,
    eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup as APIObjectStateGroup,
    eZ\Publish\Core\Repository\Values\ObjectState\ObjectState,
    eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup,
    eZ\Publish\SPI\Persistence\Content\ObjectState as SPIObjectState,
    eZ\Publish\SPI\Persistence\Content\ObjectState\Group as SPIObjectStateGroup,
    eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException;

/**
 * ObjectStateService service
 *
 * @example Examples/objectstates.php tbd.
 *
 * @package eZ\Publish\Core\Repository
 */
class ObjectStateService implements ObjectStateServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler $objectStateHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $objectStateHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->objectStateHandler = $objectStateHandler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            //'defaultSetting' => array(),
        );
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
        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $inputStruct = $this->buildCreateInputStruct(
            $objectStateGroupCreateStruct->identifier,
            $objectStateGroupCreateStruct->defaultLanguageCode,
            $objectStateGroupCreateStruct->names,
            $objectStateGroupCreateStruct->descriptions
        );

        try
        {
            $this->objectStateHandler->loadGroupByIdentifier( $inputStruct->identifier );
            throw new InvalidArgumentException(
                "objectStateGroupCreateStruct",
                "Object state group with provided identifier already exists"
            );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        $this->repository->beginTransaction();
        try
        {
            $spiObjectStateGroup = $this->objectStateHandler->createGroup( $inputStruct );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainObjectStateGroupObject( $spiObjectStateGroup );
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
        if ( !is_numeric( $objectStateGroupId ) )
            throw new InvalidArgumentValue( "objectStateGroupId", $objectStateGroupId );

        $spiObjectStateGroup = $this->objectStateHandler->loadGroup( $objectStateGroupId );

        return $this->buildDomainObjectStateGroupObject( $spiObjectStateGroup );
    }

    /**
     * Loads all object state groups
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function loadObjectStateGroups( $offset = 0, $limit = -1 )
    {
        $spiObjectStateGroups = $this->objectStateHandler->loadAllGroups( $offset, $limit );

        $objectStateGroups = array();
        foreach ( $spiObjectStateGroups as $spiObjectStateGroup )
        {
            $objectStateGroups[] = $this->buildDomainObjectStateGroupObject( $spiObjectStateGroup );
        }

        return $objectStateGroups;
    }

    /**
     * This method returns the ordered list of object states of a group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function loadObjectStates( APIObjectStateGroup $objectStateGroup )
    {
        if ( !is_numeric( $objectStateGroup->id ) )
            throw new InvalidArgumentValue( "id", $objectStateGroup->id, "ObjectStateGroup" );

        $spiObjectStates = $this->objectStateHandler->loadObjectStates( $objectStateGroup->id );

        $objectStates = array();
        foreach ( $spiObjectStates as $spiObjectState )
        {
            $objectStates[] = $this->buildDomainObjectStateObject( $spiObjectState, $objectStateGroup );
        }

        return $objectStates;
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
    public function updateObjectStateGroup( APIObjectStateGroup $objectStateGroup, ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct )
    {
        if ( !is_numeric( $objectStateGroup->id ) )
            throw new InvalidArgumentValue( "id", $objectStateGroup->id, "ObjectStateGroup" );

        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $loadedObjectStateGroup = $this->loadObjectStateGroup( $objectStateGroup->id );

        $inputStruct = $this->buildObjectStateGroupUpdateInputStruct(
            $loadedObjectStateGroup,
            $objectStateGroupUpdateStruct->identifier,
            $objectStateGroupUpdateStruct->defaultLanguageCode,
            $objectStateGroupUpdateStruct->names,
            $objectStateGroupUpdateStruct->descriptions
        );

        if ( $objectStateGroupUpdateStruct->identifier !== null )
        {
            try
            {
                $existingObjectStateGroup = $this->objectStateHandler->loadGroupByIdentifier( $inputStruct->identifier );
                if ( $existingObjectStateGroup->id != $objectStateGroup->id )
                {
                    throw new InvalidArgumentException(
                        "objectStateGroupUpdateStruct",
                        "Object state group with provided identifier already exists"
                    );
                }
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $spiObjectStateGroup = $this->objectStateHandler->updateGroup(
                $loadedObjectStateGroup->id,
                $inputStruct
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainObjectStateGroupObject( $spiObjectStateGroup );
    }

    /**
     * Deletes a object state group including all states and links to content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     */
    public function deleteObjectStateGroup( APIObjectStateGroup $objectStateGroup )
    {
        if ( !is_numeric( $objectStateGroup->id ) )
            throw new InvalidArgumentValue( "id", $objectStateGroup->id, "ObjectStateGroup" );

        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $loadedObjectStateGroup = $this->loadObjectStateGroup( $objectStateGroup->id );

        $this->repository->beginTransaction();
        try
        {
            $this->objectStateHandler->deleteGroup( $loadedObjectStateGroup->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
    public function createObjectState( APIObjectStateGroup $objectStateGroup, ObjectStateCreateStruct $objectStateCreateStruct )
    {
        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $inputStruct = $this->buildCreateInputStruct(
            $objectStateCreateStruct->identifier,
            $objectStateCreateStruct->defaultLanguageCode,
            $objectStateCreateStruct->names,
            $objectStateCreateStruct->descriptions
        );

        try
        {
            $this->objectStateHandler->loadByIdentifier( $inputStruct->identifier, $objectStateGroup->id );
            throw new InvalidArgumentException(
                "objectStateCreateStruct",
                "Object state with provided identifier already exists in provided object state group"
            );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        $this->repository->beginTransaction();
        try
        {
            $spiObjectState = $this->objectStateHandler->create( $objectStateGroup->id, $inputStruct );

            if ( is_numeric( $objectStateCreateStruct->priority ) )
            {
                $this->objectStateHandler->setPriority(
                    $spiObjectState->id,
                    (int) $objectStateCreateStruct->priority
                );

                // Reload the object state to have the updated priority,
                // considering that priorities are always incremental within a group
                $spiObjectState = $this->objectStateHandler->load( $spiObjectState->id );
            }

            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainObjectStateObject( $spiObjectState );
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
        if ( !is_numeric( $stateId ) )
            throw new InvalidArgumentValue( "stateId", $stateId );

        $spiObjectState = $this->objectStateHandler->load( $stateId );

        return $this->buildDomainObjectStateObject( $spiObjectState );
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
    public function updateObjectState( APIObjectState $objectState, ObjectStateUpdateStruct $objectStateUpdateStruct )
    {
        if ( !is_numeric( $objectState->id ) )
            throw new InvalidArgumentValue( "id", $objectState->id, "ObjectState" );

        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $loadedObjectState = $this->loadObjectState( $objectState->id );

        $inputStruct = $this->buildObjectStateUpdateInputStruct(
            $loadedObjectState,
            $objectStateUpdateStruct->identifier,
            $objectStateUpdateStruct->defaultLanguageCode,
            $objectStateUpdateStruct->names,
            $objectStateUpdateStruct->descriptions
        );

        if ( $objectStateUpdateStruct->identifier !== null )
        {
            try
            {
                $existingObjectState = $this->objectStateHandler->loadByIdentifier(
                    $inputStruct->identifier,
                    $loadedObjectState->getObjectStateGroup()->id
                );

                if ( $existingObjectState->id != $objectState->id )
                {
                    throw new InvalidArgumentException(
                        "objectStateUpdateStruct",
                        "Object state with provided identifier already exists in provided object state group"
                    );
                }
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $spiObjectState = $this->objectStateHandler->update(
                $loadedObjectState->id,
                $inputStruct
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainObjectStateObject( $spiObjectState );
    }

    /**
     * Changes the priority of the state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change priority on an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param int $priority
     */
    public function setPriorityOfObjectState( APIObjectState $objectState, $priority )
    {
        if ( !is_numeric( $objectState->id ) )
            throw new InvalidArgumentValue( "id", $objectState->id, "ObjectState" );

        if ( !is_numeric( $priority ) )
            throw new InvalidArgumentValue( "priority", $priority );

        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $loadedObjectState = $this->loadObjectState( $objectState->id );

        $this->repository->beginTransaction();
        try
        {
            $this->objectStateHandler->setPriority(
                $loadedObjectState->id,
                (int) $priority
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Deletes a object state. The state of the content objects is reset to the
     * first object state in the group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function deleteObjectState( APIObjectState $objectState )
    {
        if ( !is_numeric( $objectState->id ) )
            throw new InvalidArgumentValue( "id", $objectState->id, "ObjectState" );

        if ( $this->repository->hasAccess( 'state', 'administrate' ) !== true )
            throw new UnauthorizedException( 'state', 'administrate' );

        $loadedObjectState = $this->loadObjectState( $objectState->id );

        $this->repository->beginTransaction();
        try
        {
            $this->objectStateHandler->delete( $loadedObjectState->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
    public function setContentState( ContentInfo $contentInfo, APIObjectStateGroup $objectStateGroup, APIObjectState $objectState )
    {
        if ( !is_numeric( $contentInfo->id ) )
            throw new InvalidArgumentValue( "id", $contentInfo->id, "ContentInfo" );

        if ( !is_numeric( $objectStateGroup->id ) )
            throw new InvalidArgumentValue( "id", $objectStateGroup->id, "ObjectStateGroup" );

        if ( !is_numeric( $objectState->id ) )
            throw new InvalidArgumentValue( "id", $objectState->id, "ObjectState" );

        if ( $this->repository->canUser( 'state', 'assign', $contentInfo, $objectState ) !== true )
            throw new UnauthorizedException( 'state', 'assign' );

        $loadedObjectState = $this->loadObjectState( $objectState->id );

        if ( $loadedObjectState->getObjectStateGroup()->id != $objectStateGroup->id )
            throw new InvalidArgumentException( "objectState", "Object state does not belong to the given group" );

        $this->repository->beginTransaction();
        try
        {
            $this->objectStateHandler->setContentState(
                $contentInfo->id,
                $objectStateGroup->id,
                $loadedObjectState->id
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
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
    public function getContentState( ContentInfo $contentInfo, APIObjectStateGroup $objectStateGroup )
    {
        if ( !is_numeric( $contentInfo->id ) )
            throw new InvalidArgumentValue( "id", $contentInfo->id, "ContentInfo" );

        if ( !is_numeric( $objectStateGroup->id ) )
            throw new InvalidArgumentValue( "id", $objectStateGroup->id, "ObjectStateGroup" );

        $spiObjectState = $this->objectStateHandler->getContentState(
            $contentInfo->id,
            $objectStateGroup->id
        );

        return $this->buildDomainObjectStateObject( $spiObjectState );
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return int
     */
    public function getContentCount( APIObjectState $objectState )
    {
        if ( !is_numeric( $objectState->id ) )
            throw new InvalidArgumentValue( "id", $objectState->id, "ObjectState" );

        return $this->objectStateHandler->getContentCount(
            $objectState->id
        );
    }

    /**
     * Instantiates a new Object State Group Create Struct and sets $identified in it.
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct
     */
    public function newObjectStateGroupCreateStruct( $identifier )
    {
        $objectStateGroupCreateStruct = new ObjectStateGroupCreateStruct();
        $objectStateGroupCreateStruct->identifier = $identifier;

        return $objectStateGroupCreateStruct;
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
        $objectStateCreateStruct = new ObjectStateCreateStruct();
        $objectStateCreateStruct->identifier = $identifier;

        return $objectStateCreateStruct;
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

    /**
     * Converts the object state SPI value object to API value object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $spiObjectState
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    protected function buildDomainObjectStateObject( SPIObjectState $spiObjectState, APIObjectStateGroup $objectStateGroup = null )
    {
        $objectStateGroup = $objectStateGroup ?: $this->loadObjectStateGroup( $spiObjectState->groupId );

        return new ObjectState(
            array(
                'id' => (int) $spiObjectState->id,
                'identifier' => $spiObjectState->identifier,
                'priority' => (int) $spiObjectState->priority,
                'defaultLanguageCode' => $spiObjectState->defaultLanguage,
                'languageCodes' => $spiObjectState->languageCodes,
                'names' => $spiObjectState->name,
                'descriptions' => $spiObjectState->description,
                'objectStateGroup' => $objectStateGroup
            )
        );
    }

    /**
     * Converts the object state group SPI value object to API value object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $spiObjectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    protected function buildDomainObjectStateGroupObject( SPIObjectStateGroup $spiObjectStateGroup )
    {
        return new ObjectStateGroup(
            array(
                'id' => (int) $spiObjectStateGroup->id,
                'identifier' => $spiObjectStateGroup->identifier,
                'defaultLanguageCode' => $spiObjectStateGroup->defaultLanguage,
                'languageCodes' => $spiObjectStateGroup->languageCodes,
                'names' => $spiObjectStateGroup->name,
                'descriptions' => $spiObjectStateGroup->description
            )
        );
    }

    /**
     * Validates input for creating object states/groups and builds the InputStruct object
     *
     * @param string $identifier
     * @param string $defaultLanguageCode
     * @param string[] $names
     * @param string[] $descriptions
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function buildCreateInputStruct( $identifier, $defaultLanguageCode, $names, $descriptions )
    {
        if ( !is_string( $identifier ) || empty( $identifier ) )
            throw new InvalidArgumentValue( "identifier", $identifier );

        if ( !is_string( $defaultLanguageCode ) || empty( $defaultLanguageCode ) )
            throw new InvalidArgumentValue( "defaultLanguageCode", $defaultLanguageCode );

        if ( !is_array( $names ) || empty( $names ) )
            throw new InvalidArgumentValue( "names", $names );

        if ( !isset( $names[$defaultLanguageCode] ) )
            throw new InvalidArgumentValue( "names", $names );

        foreach ( $names as $languageCode => $name )
        {
            try
            {
                $this->repository->getContentLanguageService()->loadLanguage( $languageCode );
            }
            catch ( NotFoundException $e )
            {
                throw new InvalidArgumentValue( "names", $names );
            }

            if ( !is_string( $name ) || empty( $name ) )
                throw new InvalidArgumentValue( "names", $names );
        }

        if ( $descriptions !== null && !is_array( $descriptions ) )
            throw new InvalidArgumentValue( "descriptions", $descriptions );

        $descriptions = $descriptions !== null ? $descriptions : array();

        $inputStruct = new InputStruct();
        $inputStruct->identifier = $identifier;
        $inputStruct->defaultLanguage = $defaultLanguageCode;
        $inputStruct->name = $names;

        $inputStruct->description = array();
        foreach ( $names as $languageCode => $name )
        {
            if ( isset( $descriptions[$languageCode] ) && !empty( $descriptions[$languageCode] ) )
                $inputStruct->description[$languageCode] = $descriptions[$languageCode];
            else
                $inputStruct->description[$languageCode] = "";
        }

        return $inputStruct;
    }

    /**
     * Validates input for updating object states and builds the InputStruct object
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param string $identifier
     * @param string $defaultLanguageCode
     * @param string[] $names
     * @param string[] $descriptions
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function buildObjectStateUpdateInputStruct( APIObjectState $objectState, $identifier, $defaultLanguageCode, $names, $descriptions )
    {
        $inputStruct = new InputStruct();

        if ( $identifier !== null && ( !is_string( $identifier ) || empty( $identifier ) ) )
            throw new InvalidArgumentValue( "identifier", $identifier );

        $inputStruct->identifier = $identifier !== null ? $identifier : $objectState->identifier;

        if ( $defaultLanguageCode !== null && ( !is_string( $defaultLanguageCode ) || empty( $defaultLanguageCode ) ) )
            throw new InvalidArgumentValue( "defaultLanguageCode", $defaultLanguageCode );

        $inputStruct->defaultLanguage = $defaultLanguageCode !== null ? $defaultLanguageCode : $objectState->defaultLanguageCode;

        if ( $names !== null && ( !is_array( $names ) || empty( $names ) ) )
            throw new InvalidArgumentValue( "names", $names );

        $inputStruct->name = $names !== null ? $names : $objectState->getNames();

        if ( !isset( $inputStruct->name[$inputStruct->defaultLanguage] ) )
            throw new InvalidArgumentValue( "names", $inputStruct->name );

        foreach ( $inputStruct->name as $languageCode => $name )
        {
            try
            {
                $this->repository->getContentLanguageService()->loadLanguage( $languageCode );
            }
            catch ( NotFoundException $e )
            {
                throw new InvalidArgumentValue( "names", $inputStruct->name );
            }

            if ( !is_string( $name ) || empty( $name ) )
                throw new InvalidArgumentValue( "names", $inputStruct->name );
        }

        if ( $descriptions !== null && !is_array( $descriptions ) )
            throw new InvalidArgumentValue( "descriptions", $descriptions );

        $descriptions = $descriptions !== null ? $descriptions : $objectState->getDescriptions();
        $descriptions = $descriptions !== null ? $descriptions : array();

        $inputStruct->description = array();
        foreach ( $inputStruct->name as $languageCode => $name )
        {
            if ( isset( $descriptions[$languageCode] ) && !empty( $descriptions[$languageCode] ) )
                $inputStruct->description[$languageCode] = $descriptions[$languageCode];
            else
                $inputStruct->description[$languageCode] = "";
        }

        return $inputStruct;
    }

    /**
     * Validates input for updating object state groups and builds the InputStruct object
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param string $identifier
     * @param string $defaultLanguageCode
     * @param string[] $names
     * @param string[] $descriptions
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function buildObjectStateGroupUpdateInputStruct( APIObjectStateGroup $objectStateGroup, $identifier, $defaultLanguageCode, $names, $descriptions )
    {
        $inputStruct = new InputStruct();

        if ( $identifier !== null && ( !is_string( $identifier ) || empty( $identifier ) ) )
            throw new InvalidArgumentValue( "identifier", $identifier );

        $inputStruct->identifier = $identifier !== null ? $identifier : $objectStateGroup->identifier;

        if ( $defaultLanguageCode !== null && ( !is_string( $defaultLanguageCode ) || empty( $defaultLanguageCode ) ) )
            throw new InvalidArgumentValue( "defaultLanguageCode", $defaultLanguageCode );

        $inputStruct->defaultLanguage = $defaultLanguageCode !== null ? $defaultLanguageCode : $objectStateGroup->defaultLanguageCode;

        if ( $names !== null && ( !is_array( $names ) || empty( $names ) ) )
            throw new InvalidArgumentValue( "names", $names );

        $inputStruct->name = $names !== null ? $names : $objectStateGroup->getNames();

        if ( !isset( $inputStruct->name[$inputStruct->defaultLanguage] ) )
            throw new InvalidArgumentValue( "names", $inputStruct->name );

        foreach ( $inputStruct->name as $languageCode => $name )
        {
            try
            {
                $this->repository->getContentLanguageService()->loadLanguage( $languageCode );
            }
            catch ( NotFoundException $e )
            {
                throw new InvalidArgumentValue( "names", $inputStruct->name );
            }

            if ( !is_string( $name ) || empty( $name ) )
                throw new InvalidArgumentValue( "names", $inputStruct->name );
        }

        if ( $descriptions !== null && !is_array( $descriptions ) )
            throw new InvalidArgumentValue( "descriptions", $descriptions );

        $descriptions = $descriptions !== null ? $descriptions : $objectStateGroup->getDescriptions();
        $descriptions = $descriptions !== null ? $descriptions : array();

        $inputStruct->description = array();
        foreach ( $inputStruct->name as $languageCode => $name )
        {
            if ( isset( $descriptions[$languageCode] ) && !empty( $descriptions[$languageCode] ) )
                $inputStruct->description[$languageCode] = $descriptions[$languageCode];
            else
                $inputStruct->description[$languageCode] = "";
        }

        return $inputStruct;
    }
}
