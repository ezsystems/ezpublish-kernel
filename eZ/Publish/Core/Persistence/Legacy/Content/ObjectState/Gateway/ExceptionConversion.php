<?php
/**
 * File containing the ObjectState Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway,
    eZ\Publish\SPI\Persistence\Content\ObjectState,
    eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

/**
 * ObjectState Gateway
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Loads data for an object state
     *
     * @param mixed $stateId
     * @return array
     */
    public function loadObjectStateData( $stateId )
    {
        try
        {
            return $this->innerGateway->loadObjectStateData( $stateId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for an object state by identifier
     *
     * @param string $identifier
     * @param mixed $groupId
     * @return array
     */
    public function loadObjectStateDataByIdentifier( $identifier, $groupId )
    {
        try
        {
            return $this->innerGateway->loadObjectStateDataByIdentifier( $identifier, $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for all object states belonging to group with $groupId ID
     *
     * @param mixed $groupId
     * @return array
     */
    public function loadObjectStateListData( $groupId )
    {
        try
        {
            return $this->innerGateway->loadObjectStateListData( $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for an object state group
     *
     * @param mixed $groupId
     * @return array
     */
    public function loadObjectStateGroupData( $groupId )
    {
        try
        {
            return $this->innerGateway->loadObjectStateGroupData( $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for an object state group by identifier
     *
     * @param string $identifier
     * @return array
     */
    public function loadObjectStateGroupDataByIdentifier( $identifier )
    {
        try
        {
            return $this->innerGateway->loadObjectStateGroupDataByIdentifier( $identifier );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for all object state groups, filtered by $offset and $limit
     *
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function loadObjectStateGroupListData( $offset, $limit )
    {
        try
        {
            return $this->innerGateway->loadObjectStateGroupListData( $offset, $limit );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new object state into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     * @param int $groupId
     */
    public function insertObjectState( ObjectState $objectState, $groupId )
    {
        try
        {
            return $this->innerGateway->insertObjectState( $objectState, $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates the stored object state with provided data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    public function updateObjectState( ObjectState $objectState )
    {
        try
        {
            return $this->innerGateway->updateObjectState( $objectState );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes object state identified by $stateId
     *
     * @param int $stateId
     */
    public function deleteObjectState( $stateId )
    {
        try
        {
            return $this->innerGateway->deleteObjectState( $stateId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Update object state links from $oldStateId to $newStateId
     *
     * @param int $oldStateId
     * @param int $newStateId
     */
    public function updateObjectStateLinks( $oldStateId, $newStateId )
    {
        try
        {
            return $this->innerGateway->updateObjectStateLinks( $oldStateId, $newStateId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes object state links identified by $stateId
     *
     * @param int $stateId
     */
    public function deleteObjectStateLinks( $stateId )
    {
        try
        {
            return $this->innerGateway->deleteObjectStateLinks( $stateId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new object state group into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function insertObjectStateGroup( Group $objectStateGroup )
    {
        try
        {
            return $this->innerGateway->insertObjectStateGroup( $objectStateGroup );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates the stored object state group with provided data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function updateObjectStateGroup( Group $objectStateGroup )
    {
        try
        {
            return $this->innerGateway->updateObjectStateGroup( $objectStateGroup );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the object state group identified by $groupId
     *
     * @param mixed $groupId
     */
    public function deleteObjectStateGroup( $groupId )
    {
        try
        {
            return $this->innerGateway->deleteObjectStateGroup( $groupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Sets the object state $stateId to content with $contentId ID
     *
     * @param mixed $contentId
     * @param mixed $groupId
     * @param mixed $stateId
     */
    public function setContentState( $contentId, $groupId, $stateId )
    {
        try
        {
            return $this->innerGateway->setContentState( $contentId, $groupId, $stateId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads object state data for $contentId content from $stateGroupId state group
     *
     * @param int $contentId
     * @param int $stateGroupId
     *
     * @return array
     */
    public function loadObjectStateDataForContent( $contentId, $stateGroupId )
    {
        try
        {
            return $this->innerGateway->loadObjectStateDataForContent( $contentId, $stateGroupId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param mixed $stateId
     * @return int
     */
    public function getContentCount( $stateId )
    {
        try
        {
            return $this->innerGateway->getContentCount( $stateId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates the object state priority to provided value
     *
     * @param mixed $stateId
     * @param int $priority
     */
    public function updateObjectStatePriority( $stateId, $priority )
    {
        try
        {
            return $this->innerGateway->updateObjectStatePriority( $stateId, $priority );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
