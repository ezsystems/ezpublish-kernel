<?php
/**
 * File containing the ObjectState ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

/**
 * ObjectState ezcDatabase Gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $maskGenerator;

    /**
     * Creates a new EzcDatabase ObjectState Gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $maskGenerator
     */
    public function __construct( EzcDbHandler $dbHandler, MaskGenerator $maskGenerator )
    {
        $this->dbHandler = $dbHandler;
        $this->maskGenerator = $maskGenerator;
    }

    /**
     * Loads data for an object state
     *
     * @param mixed $stateId
     *
     * @return array
     */
    public function loadObjectStateData( $stateId )
    {
        $query = $this->createObjectStateFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcobj_state' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads data for an object state by identifier
     *
     * @param string $identifier
     * @param mixed $groupId
     *
     * @return array
     */
    public function loadObjectStateDataByIdentifier( $identifier, $groupId )
    {
        $query = $this->createObjectStateFindQuery();
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'identifier', 'ezcobj_state' ),
                    $query->bindValue( $identifier, null, \PDO::PARAM_STR )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'group_id', 'ezcobj_state' ),
                    $query->bindValue( $groupId, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads data for all object states belonging to group with $groupId ID
     *
     * @param mixed $groupId
     *
     * @return array
     */
    public function loadObjectStateListData( $groupId )
    {
        $query = $this->createObjectStateFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'group_id', 'ezcobj_state' ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        )->orderBy( $this->dbHandler->quoteColumn( 'priority', 'ezcobj_state' ), $query::ASC );

        $statement = $query->prepare();
        $statement->execute();

        $rows = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $rows[$row['ezcobj_state_id']][] = $row;
        }

        return array_values( $rows );
    }

    /**
     * Loads data for an object state group
     *
     * @param mixed $groupId
     *
     * @return array
     */
    public function loadObjectStateGroupData( $groupId )
    {
        $query = $this->createObjectStateGroupFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcobj_state_group' ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads data for an object state group by identifier
     *
     * @param string $identifier
     *
     * @return array
     */
    public function loadObjectStateGroupDataByIdentifier( $identifier )
    {
        $query = $this->createObjectStateGroupFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'identifier', 'ezcobj_state_group' ),
                $query->bindValue( $identifier, null, \PDO::PARAM_STR )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads data for all object state groups, filtered by $offset and $limit
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function loadObjectStateGroupListData( $offset, $limit )
    {
        $query = $this->createObjectStateGroupFindQuery();
        $query->limit( $limit > 0 ? $limit : PHP_INT_MAX, $offset );

        $statement = $query->prepare();
        $statement->execute();

        $rows = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $rows[$row['ezcobj_state_group_id']][] = $row;
        }

        return array_values( $rows );
    }

    /**
     * Inserts a new object state into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     * @param int $groupId
     */
    public function insertObjectState( ObjectState $objectState, $groupId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->max( $this->dbHandler->quoteColumn( 'priority' ) )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'group_id' ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $maxPriority = $statement->fetchColumn();

        $objectState->priority = $maxPriority === null ? 0 : (int)$maxPriority + 1;
        $objectState->groupId = (int)$groupId;

        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcobj_state', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'group_id' ),
            $query->bindValue( $objectState->groupId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'default_language_id' ),
            $query->bindValue(
                $this->maskGenerator->generateLanguageIndicator( $objectState->defaultLanguage, false ),
                null, \PDO::PARAM_INT
            )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $objectState->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $query->bindValue( $this->generateLanguageMask( $objectState->languageCodes ), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'priority' ),
            $query->bindValue( $objectState->priority, null, \PDO::PARAM_INT )
        );

        $query->prepare()->execute();

        $objectState->id = (int)$this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcobj_state', 'id' )
        );

        $this->insertObjectStateTranslations( $objectState );

        // If this is a first state in group, assign it to all content objects
        if ( $maxPriority === null )
        {
            // @todo Hm... How do we perform this with query object?
            $this->dbHandler->query(
                "INSERT INTO ezcobj_state_link (contentobject_id, contentobject_state_id)
                SELECT id, {$objectState->id} FROM ezcontentobject"
            );
        }
    }

    /**
     * Updates the stored object state with provided data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    public function updateObjectState( ObjectState $objectState )
    {
        // First update the state
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->set(
            $this->dbHandler->quoteColumn( 'default_language_id' ),
            $query->bindValue(
                $this->maskGenerator->generateLanguageIndicator( $objectState->defaultLanguage, false ),
                null, \PDO::PARAM_INT
            )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $objectState->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $query->bindValue( $this->generateLanguageMask( $objectState->languageCodes ), null, \PDO::PARAM_INT )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $objectState->id, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();

        // And then refresh object state translations
        // by removing existing ones and adding new ones
        $this->deleteObjectStateTranslations( $objectState->id );
        $this->insertObjectStateTranslations( $objectState );
    }

    /**
     * Deletes object state identified by $stateId
     *
     * @param int $stateId
     */
    public function deleteObjectState( $stateId )
    {
        $this->deleteObjectStateTranslations( $stateId );

        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Update object state links to $newStateId
     *
     * @param int $oldStateId
     * @param int $newStateId
     */
    public function updateObjectStateLinks( $oldStateId, $newStateId )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable( 'ezcobj_state_link' )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
            $query->bindValue( $newStateId, null, \PDO::PARAM_INT )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $oldStateId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Deletes object state links identified by $stateId
     *
     * @param int $stateId
     */
    public function deleteObjectStateLinks( $stateId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcobj_state_link' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Inserts a new object state group into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function insertObjectStateGroup( Group $objectStateGroup )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( 'ezcobj_state_group' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcobj_state_group', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'default_language_id' ),
            $query->bindValue(
                $this->maskGenerator->generateLanguageIndicator( $objectStateGroup->defaultLanguage, false ),
                null, \PDO::PARAM_INT
            )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $objectStateGroup->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $query->bindValue( $this->generateLanguageMask( $objectStateGroup->languageCodes ), null, \PDO::PARAM_INT )
        );

        $query->prepare()->execute();

        $objectStateGroup->id = (int)$this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcobj_state_group', 'id' )
        );

        $this->insertObjectStateGroupTranslations( $objectStateGroup );
    }

    /**
     * Updates the stored object state group with provided data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function updateObjectStateGroup( Group $objectStateGroup )
    {
        // First update the group
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable( 'ezcobj_state_group' )
        )->set(
            $this->dbHandler->quoteColumn( 'default_language_id' ),
            $query->bindValue(
                $this->maskGenerator->generateLanguageIndicator( $objectStateGroup->defaultLanguage, false ),
                null, \PDO::PARAM_INT
            )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $objectStateGroup->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $query->bindValue( $this->generateLanguageMask( $objectStateGroup->languageCodes ), null, \PDO::PARAM_INT )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $objectStateGroup->id, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();

        // And then refresh group translations
        // by removing old ones and adding new ones
        $this->deleteObjectStateGroupTranslations( $objectStateGroup->id );
        $this->insertObjectStateGroupTranslations( $objectStateGroup );
    }

    /**
     * Deletes the object state group identified by $groupId
     *
     * @param mixed $groupId
     */
    public function deleteObjectStateGroup( $groupId )
    {
        $this->deleteObjectStateGroupTranslations( $groupId );

        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcobj_state_group' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
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
        // First find out if $contentId is related to existing states in $groupId
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcobj_state' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->innerJoin(
            $this->dbHandler->quoteTable( 'ezcobj_state_link' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcobj_state' ),
                $this->dbHandler->quoteColumn( 'contentobject_state_id', 'ezcobj_state_link' )
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'group_id', 'ezcobj_state' ),
                    $query->bindValue( $groupId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcobj_state_link' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

        if ( !empty( $rows ) )
        {
            // We already have a state assigned to $contentId, update to new one
            $query = $this->dbHandler->createUpdateQuery();
            $query->update(
                $this->dbHandler->quoteTable( 'ezcobj_state_link' )
            )->set(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id' ),
                        $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                        $query->bindValue( $rows[0]['ezcobj_state_id'], null, \PDO::PARAM_INT )
                    )
                )
            );

            $query->prepare()->execute();
        }
        else
        {
            // No state assigned to $contentId from specified group, create assignment
            $query = $this->dbHandler->createInsertQuery();
            $query->insertInto(
                $this->dbHandler->quoteTable( 'ezcobj_state_link' )
            )->set(
                $this->dbHandler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentId, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            );

            $query->prepare()->execute();
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
        $query = $this->createObjectStateFindQuery();
        $query->innerJoin(
            $this->dbHandler->quoteTable( 'ezcobj_state_link' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcobj_state' ),
                $this->dbHandler->quoteColumn( 'contentobject_state_id', 'ezcobj_state_link' )
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'group_id', 'ezcobj_state' ),
                    $query->bindValue( $stateGroupId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcobj_state_link' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param mixed $stateId
     *
     * @return int
     */
    public function getContentCount( $stateId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias( $query->expr->count( '*' ), 'count' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state_link' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $count = $statement->fetchColumn();

        return $count !== null ? (int)$count : 0;
    }

    /**
     * Updates the object state priority to provided value
     *
     * @param mixed $stateId
     * @param int $priority
     */
    public function updateObjectStatePriority( $stateId, $priority )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->set(
            $this->dbHandler->quoteColumn( 'priority' ),
            $query->bindValue( $priority, null, \PDO::PARAM_INT )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Creates a generalized query for fetching object state(s)
     *
     * @return \ezcQuerySelect
     */
    protected function createObjectStateFindQuery()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            // Object state
            $this->dbHandler->aliasedColumn( $query, 'default_language_id', 'ezcobj_state' ),
            $this->dbHandler->aliasedColumn( $query, 'group_id', 'ezcobj_state' ),
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcobj_state' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezcobj_state' ),
            $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcobj_state' ),
            $this->dbHandler->aliasedColumn( $query, 'priority', 'ezcobj_state' ),
            // Object state language
            $this->dbHandler->aliasedColumn( $query, 'description', 'ezcobj_state_language' ),
            $this->dbHandler->aliasedColumn( $query, 'language_id', 'ezcobj_state_language' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcobj_state_language' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->innerJoin(
            $this->dbHandler->quoteTable( 'ezcobj_state_language' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcobj_state' ),
                $this->dbHandler->quoteColumn( 'contentobject_state_id', 'ezcobj_state_language' )
            )
        );

        return $query;
    }

    /**
     * Creates a generalized query for fetching object state group(s)
     *
     * @return \ezcQuerySelect
     */
    protected function createObjectStateGroupFindQuery()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            // Object state group
            $this->dbHandler->aliasedColumn( $query, 'default_language_id', 'ezcobj_state_group' ),
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcobj_state_group' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezcobj_state_group' ),
            $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcobj_state_group' ),
            // Object state group language
            $this->dbHandler->aliasedColumn( $query, 'description', 'ezcobj_state_group_language' ),
            $this->dbHandler->aliasedColumn( $query, 'language_id', 'ezcobj_state_group_language' ),
            $this->dbHandler->aliasedColumn( $query, 'real_language_id', 'ezcobj_state_group_language' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcobj_state_group_language' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state_group' )
        )->innerJoin(
            $this->dbHandler->quoteTable( 'ezcobj_state_group_language' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcobj_state_group' ),
                $this->dbHandler->quoteColumn( 'contentobject_state_group_id', 'ezcobj_state_group_language' )
            )
        );

        return $query;
    }

    /**
     * Inserts object state group translations into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    protected function insertObjectStateTranslations( ObjectState $objectState )
    {
        foreach ( $objectState->languageCodes as $languageCode )
        {
            $query = $this->dbHandler->createInsertQuery();
            $query->insertInto(
                $this->dbHandler->quoteTable( 'ezcobj_state_language' )
            )->set(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $objectState->id, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteColumn( 'description' ),
                $query->bindValue( $objectState->description[$languageCode] )
            )->set(
                $this->dbHandler->quoteColumn( 'name' ),
                $query->bindValue( $objectState->name[$languageCode] )
            )->set(
                $this->dbHandler->quoteColumn( 'language_id' ),
                $query->bindValue(
                    $this->maskGenerator->generateLanguageIndicator(
                        $languageCode, $languageCode === $objectState->defaultLanguage
                    ),
                    null, \PDO::PARAM_INT
                )
            );

            $query->prepare()->execute();
        }
    }

    /**
     * Deletes all translations of the $stateId state
     *
     * @param mixed $stateId
     */
    protected function deleteObjectStateTranslations( $stateId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcobj_state_language' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_state_id' ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Inserts object state group translations into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    protected function insertObjectStateGroupTranslations( Group $objectStateGroup )
    {
        foreach ( $objectStateGroup->languageCodes as $languageCode )
        {
            $languageId = $this->maskGenerator->generateLanguageIndicator(
                $languageCode, $languageCode === $objectStateGroup->defaultLanguage
            );

            $query = $this->dbHandler->createInsertQuery();
            $query->insertInto(
                $this->dbHandler->quoteTable( 'ezcobj_state_group_language' )
            )->set(
                $this->dbHandler->quoteColumn( 'contentobject_state_group_id' ),
                $query->bindValue( $objectStateGroup->id, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteColumn( 'description' ),
                $query->bindValue( $objectStateGroup->description[$languageCode] )
            )->set(
                $this->dbHandler->quoteColumn( 'name' ),
                $query->bindValue( $objectStateGroup->name[$languageCode] )
            )->set(
                $this->dbHandler->quoteColumn( 'language_id' ),
                $query->bindValue( $languageId, null, \PDO::PARAM_INT )
            )->set(
                $this->dbHandler->quoteColumn( 'real_language_id' ),
                $query->bindValue( $languageId & ~1, null, \PDO::PARAM_INT )
            );

            $query->prepare()->execute();
        }
    }

    /**
     * Deletes all translations of the $groupId state group
     *
     * @param mixed $groupId
     */
    protected function deleteObjectStateGroupTranslations( $groupId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcobj_state_group_language' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_state_group_id' ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Generates language mask from provided language codes
     * Also sets always available bit
     *
     * @param array $languageCodes
     *
     * @return int
     */
    protected function generateLanguageMask( array $languageCodes )
    {
        $maskLanguageCodes = array();
        foreach ( $languageCodes as $languageCode )
        {
            $maskLanguageCodes[$languageCode] = 1;
        }
        $maskLanguageCodes['always-available'] = 1;

        return $this->maskGenerator->generateLanguageMask( $maskLanguageCodes );
    }
}
