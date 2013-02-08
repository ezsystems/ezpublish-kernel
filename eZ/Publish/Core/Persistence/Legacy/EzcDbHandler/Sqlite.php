<?php
/**
 * File containing a wrapper for the DB handler
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

/**
 * Wrapper class for the zeta components database handler, providing some
 * additional utility functions.
 *
 * Functions as a full proxy to the zeta components database class.
 */
class Sqlite extends EzcDbHandler
{
    protected $lastInsertedIds = array();

    /**
     * Get auto increment value
     *
     * Returns the value used for autoincrement tables. Usually this will just
     * be null. In case for sequence based RDBMS this method can return a
     * proper value for the given column.
     *
     * @param string $table
     * @param string $column
     *
     * @return mixed
     */
    public function getAutoIncrementValue( $table, $column )
    {
        if ( ( $table === "ezcontentobject_attribute" ) && ( $column === "id" ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->ezcDbHandler->createSelectQuery();
            $q->select( $q->expr->max( "id" ) )->from( "ezcontentobject_attribute" );
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds["ezcontentobject_attribute.id"] = (int)$statement->fetchColumn() + 1;
            return $this->lastInsertedIds["ezcontentobject_attribute.id"];
        }

        if ( ( $table === "ezcontentclass" ) && ( $column === "id" ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->ezcDbHandler->createSelectQuery();
            $q->select( $q->expr->max( "id" ) )->from( "ezcontentclass" );
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds["ezcontentclass.id"] = (int)$statement->fetchColumn() + 1;
            return $this->lastInsertedIds["ezcontentclass.id"];
        }

        if ( ( $table === "ezcontentclass_attribute" ) && ( $column === "id" ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->ezcDbHandler->createSelectQuery();
            $q->select( $q->expr->max( "id" ) )->from( "ezcontentclass_attribute" );
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds["ezcontentclass_attribute.id"] = (int)$statement->fetchColumn() + 1;
            return $this->lastInsertedIds["ezcontentclass_attribute.id"];
        }

        return parent::getAutoIncrementValue( $table, $column );
    }

    public function lastInsertId( $sequenceName )
    {
        if ( isset( $this->lastInsertedIds[$sequenceName] ) )
        {
            $lastInsertId = $this->lastInsertedIds[$sequenceName];
            unset( $this->lastInsertedIds[$sequenceName] );
            return $lastInsertId;
        }
        else
        {
            return $this->ezcDbHandler->lastInsertId( $sequenceName );
        }
    }

    public function getSequenceName( $table, $column )
    {
        if ( ( $table === "ezcontentobject_attribute" ) && (  $column === "id" ) )
        {
            return "{$table}.{$column}";
        }

        if ( ( $table === "ezcontentclass" ) && ( $column === "id" ) )
        {
            return "{$table}.{$column}";
        }

        if ( ( $table === "ezcontentclass_attribute" ) && (  $column === "id" ) )
        {
            return "{$table}.{$column}";
        }

        return parent::getSequenceName( $table, $column );
    }
}

