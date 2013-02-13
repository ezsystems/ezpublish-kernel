<?php
/**
 * File containing the abstract Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;

use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 *
 */
class LegacyStorage extends Gateway
{
    const URL_TABLE = "ezurl";

    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( ! ( $dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler ) )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new \RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        if ( ( $row = $this->fetchByLink( $field->value->externalData ) ) !== false )
            $urlId = $row["id"];
        else
            $urlId = $this->insert( $versionInfo, $field );

        $field->value->data["urlId"] = $urlId;

        // Signals that the Value has been modified and that an update is to be performed
        return true;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway
     */
    public function getFieldData( Field $field )
    {
        $url = $this->fetchById( $field->value->data["urlId"] );
        $field->value->externalData = $url["url"];
    }

    /**
     * Fetches a row in ezurl table referenced by its $id
     *
     * @param mixed $id
     *
     * @return null|array Hash with columns as keys or null if no entry can be found
     */
    private function fetchById( $id )
    {
        $dbHandler = $this->getConnection();

        $q = $dbHandler->createSelectQuery();
        $e = $q->expr;
        $q->select( "*" )
            ->from( $dbHandler->quoteTable( self::URL_TABLE ) )
            ->where(
                $e->eq( "id", $q->bindValue( $id, null, \PDO::PARAM_INT ) )
            );

        $statement = $q->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( count( $rows ) )
        {
            return $rows[0];
        }

        return false;
    }

    /**
     * Fetches a row in ezurl table referenced by $link
     *
     * @param string $link
     *
     * @return null|array Hash with columns as keys or null if no entry can be found
     */
    private function fetchByLink( $link )
    {
        $dbHandler = $this->getConnection();

        $q = $dbHandler->createSelectQuery();
        $e = $q->expr;
        $q->select( "*" )
            ->from( $dbHandler->quoteTable( self::URL_TABLE ) )
            ->where(
                $e->eq( "url", $q->bindValue( $link ) )
            );

        $statement = $q->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( count( $rows ) )
        {
            return $rows[0];
        }

        return false;
    }

    /**
     * Inserts a new entry in ezurl table with $field value data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     *
     * @return mixed
     */
    private function insert( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        $time = time();

        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable( self::URL_TABLE )
        )->set(
            $dbHandler->quoteColumn( "created" ),
            $q->bindValue( $time, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "modified" ),
            $q->bindValue( $time, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "original_url_md5" ),
            $q->bindValue( md5( $field->value->externalData ) )
        )->set(
            $dbHandler->quoteColumn( "url" ),
            $q->bindValue( $field->value->externalData )
        );

        $q->prepare()->execute();

        return $dbHandler->lastInsertId(
            $dbHandler->getSequenceName( self::URL_TABLE, "id" )
        );
    }
}
