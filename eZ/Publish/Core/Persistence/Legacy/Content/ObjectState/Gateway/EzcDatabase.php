<?php
/**
 * File containing the ObjectState ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

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
     * Creates a new EzcDatabase ObjectState Gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     */
    public function __construct( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Loads data for an object state
     *
     * @param mixed $stateId
     * @return array
     */
    public function loadObjectStateData( $stateId )
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
                $this->dbHandler->quoteColumn(
                    'id',
                    'ezcobj_state'
                ),
                $this->dbHandler->quoteColumn(
                    'contentobject_state_id',
                    'ezcobj_state_language'
                )
            )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn(
                    'id',
                    'ezcobj_state'
                ),
                $query->bindValue( $stateId, null, \PDO::PARAM_INT )
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
     * @return array
     */
    public function loadObjectStateListData( $groupId )
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
                $this->dbHandler->quoteColumn(
                    'id',
                    'ezcobj_state'
                ),
                $this->dbHandler->quoteColumn(
                    'contentobject_state_id',
                    'ezcobj_state_language'
                )
            )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn(
                    'group_id',
                    'ezcobj_state'
                ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
            )
        );

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
     * @return array
     */
    public function loadObjectStateGroupData( $groupId )
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
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcobj_state_group_language' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state_group' )
        )->innerJoin(
            $this->dbHandler->quoteTable( 'ezcobj_state_group_language' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn(
                    'id',
                    'ezcobj_state_group'
                ),
                $this->dbHandler->quoteColumn(
                    'contentobject_state_group_id',
                    'ezcobj_state_group_language'
                )
            )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn(
                    'id',
                    'ezcobj_state_group'
                ),
                $query->bindValue( $groupId, null, \PDO::PARAM_INT )
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
     * @return array
     */
    public function loadObjectStateGroupListData( $offset, $limit )
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
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcobj_state_group_language' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state_group' )
        )->innerJoin(
            $this->dbHandler->quoteTable( 'ezcobj_state_group_language' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn(
                    'id',
                    'ezcobj_state_group'
                ),
                $this->dbHandler->quoteColumn(
                    'contentobject_state_group_id',
                    'ezcobj_state_group_language'
                )
            )
        )->limit( $limit, $offset );

        $statement = $query->prepare();
        $statement->execute();

        $rows = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $rows[$row['ezcobj_state_group_id']][] = $row;
        }

        return array_values( $rows );
    }
}
