<?php
/**
 * File containing the UrlWildcard ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * UrlWildcard Gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new EzcDatabase Section Gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     */
    public function __construct ( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Inserts the given UrlWildcard
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $urlWildcard
     *
     * @return int
     */
    public function insertUrlWildcard( UrlWildcard $urlWildcard )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->set(
            $this->dbHandler->quoteColumn( "destination_url" ),
            $query->bindValue( $urlWildcard->destinationUrl )
        )->set(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->getAutoIncrementValue( "ezurlwildcard", "id" )
        )->set(
            $this->dbHandler->quoteColumn( "source_url" ),
            $query->bindValue( $urlWildcard->sourceUrl )
        )->set(
            $this->dbHandler->quoteColumn( "type" ),
            $query->bindValue(
                $urlWildcard->forward ? 1 : 2
            )
        );

        $query->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( "ezurlwildcard", "id" )
        );
    }

    /**
     * Deletes the UrlWildcard with given $id
     *
     * @param mixed $id
     *
     * @return void
     */
    public function deleteUrlWildcard( $id )
    {
        $q = $this->dbHandler->createDeleteQuery();
        $q->deleteFrom(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( "id" ),
                $q->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Loads an array with data about UrlWildcard with $id
     *
     * @param mixed $id
     *
     * @return array
     */
    public function loadUrlWildcardData( $id )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            "*"
        )->from(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( "id" ),
                $q->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );
        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads an array with data about UrlWildcards (paged)
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    public function loadUrlWildcardsData( $offset, $limit )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q->select(
            "*"
        )->from(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->limit( $limit > 0 ? $limit : PHP_INT_MAX, $offset );

        $stmt = $q->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }
}
