<?php
/**
 * File containing the Section ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Section\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Section\Gateway,
    ezp\Persistence\Content\Section,
    ezp\Persistence\Storage\Legacy\EzcDbHandler;

/**
 * Section Handler
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @param ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new EzcDatabase Section Gateway
     *
     * @param ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     */
    public function __construct ( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Inserts a new section with $name and $identifier
     *
     * @param string $name
     * @param string $identifier
     * @return int The ID of the new section
     */
    public function insertSection( $name, $identifier )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( 'ezsection' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezsection', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $query->bindValue( $name )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $identifier )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezsection', 'id' )
        );
    }

    /**
     * Updates section with $id to have $name and $identifier
     *
     * @param int $id
     * @param string $name
     * @param string $identifier
     * @return void
     */
    public function updateSection( $id, $name, $identifier )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable( 'ezsection' )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $query->bindValue( $name )
        )->set(
            $this->dbHandler->quoteColumn( 'identifier' ),
            $query->bindValue( $identifier )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Loads data for section with $id
     *
     * @param int $id
     * @return string[][]
     */
    public function loadSectionData( $id )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->quoteColumn( 'identifier' ),
            $this->dbHandler->quoteColumn( 'name' )
        )->from(
            $this->dbHandler->quoteTable( 'ezsection' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Counts the number of content objects assigned to section with $id
     *
     * @param int $id
     * @return int
     */
    public function countContentObjectsInSection( $id )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias(
                $query->expr->count(
                    $this->dbHandler->quoteColumn( 'id' )
                ),
                'content_count'
            )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'section_id' ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Deletes the Section with $id
     *
     * @param int $id
     * @return void
     */
    public function deleteSection( $id )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezsection' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Inserts the assignment of $contentId to $sectionId
     *
     * @param int $sectionId
     * @param int $contentId
     * @return void
     */
    public function assignSectionToContent( $sectionId, $contentId )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->set(
                $this->dbHandler->quoteColumn( 'section_id' ),
                $query->bindValue( $sectionId, null, \PDO::PARAM_INT )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $query->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
    }
}
