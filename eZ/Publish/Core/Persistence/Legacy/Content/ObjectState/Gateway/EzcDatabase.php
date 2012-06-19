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
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\SPI\Persistence\Content\ObjectState,
    eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

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

    /**
     * Inserts a new object state group into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group $objectStateGroup
     */
    public function insertObjectStateGroup( Group $objectStateGroup )
    {
        $languageCodes = array();
        foreach ( $objectStateGroup->languageCodes as $languageCode )
        {
            $languageCodes[$languageCode] = 1;
        }
        $languageCodes['always-available'] = 1;

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
                null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $objectStateGroup->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $query->bindValue( $this->maskGenerator->generateLanguageMask( $languageCodes ) )
        );

        $query->prepare()->execute();

        $objectStateGroup->id = (int) $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcobj_state_group', 'id' )
        );

        foreach ( $objectStateGroup->languageCodes as $languageCode )
        {
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
                $query->bindValue(
                    $this->maskGenerator->generateLanguageIndicator(
                        $languageCode, $languageCode === $objectStateGroup->defaultLanguage
                    ),
                    null, \PDO::PARAM_INT
                )
            );

            $query->prepare()->execute();
        }
    }

    /**
     * Inserts a new object state into database
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState $objectState
     */
    public function insertObjectState( ObjectState $objectState )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->max( $this->dbHandler->quoteColumn( 'priority' ) )
        )->from(
            $this->dbHandler->quoteTable( 'ezcobj_state' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn(
                    'group_id',
                    'ezcobj_state'
                ),
                $query->bindValue( $objectState->groupId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $objectState->priority = (int) $statement->fetchColumn() + 1;

        $languageCodes = array();
        foreach ( $objectState->languageCodes as $languageCode )
        {
            $languageCodes[$languageCode] = 1;
        }
        $languageCodes['always-available'] = 1;

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
                null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $objectState->identifier )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $query->bindValue( $this->maskGenerator->generateLanguageMask( $languageCodes ) )
        )->set(
            $this->dbHandler->quoteColumn( 'priority' ),
            $query->bindValue( $objectState->priority, null, \PDO::PARAM_INT )
        );

        $query->prepare()->execute();

        $objectState->id = (int) $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcobj_state', 'id' )
        );

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
}
