<?php
/**
 * File containing the UrlWildcard ezcDatabase Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * UrlWildcard Gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems
     */
    const MAX_LIMIT = 1073741824;

    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
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
     * @return mixed
     */
    public function insertUrlWildcard( UrlWildcard $urlWildcard )
    {
        /** @var $query \ezcQueryInsert */
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->set(
            $this->dbHandler->quoteColumn( "destination_url" ),
            $query->bindValue(
                trim( $urlWildcard->destinationUrl, "/ " ),
                null,
                \PDO::PARAM_STR
            )
        )->set(
            $this->dbHandler->quoteColumn( "id" ),
            $this->dbHandler->getAutoIncrementValue( "ezurlwildcard", "id" )
        )->set(
            $this->dbHandler->quoteColumn( "source_url" ),
            $query->bindValue(
                trim( $urlWildcard->sourceUrl, "/ " ),
                null,
                \PDO::PARAM_STR
            )
        )->set(
            $this->dbHandler->quoteColumn( "type" ),
            $query->bindValue(
                $urlWildcard->forward ? 1 : 2,
                null,
                \PDO::PARAM_INT
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
        /** @var $query \ezcQueryDelete */
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( "id" ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );
        $query->prepare()->execute();
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
        /** @var $query \ezcQuerySelect */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            "*"
        )->from(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( "id" ),
                $query->bindValue( $id, null, \PDO::PARAM_INT )
            )
        );
        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetch( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads an array with data about UrlWildcards (paged)
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    public function loadUrlWildcardsData( $offset = 0, $limit = -1 )
    {
        $limit = $limit === -1 ? self::MAX_LIMIT : $limit;

        /** @var $query \ezcQuerySelect */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            "*"
        )->from(
            $this->dbHandler->quoteTable( "ezurlwildcard" )
        )->limit(
            $limit > 0 ?
                $limit :
                self::MAX_LIMIT,
            $offset
        );

        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll( \PDO::FETCH_ASSOC );
    }
}
