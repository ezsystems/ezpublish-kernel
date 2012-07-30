<?php
/**
 * File containing the abstract Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Relation\RelationStorage\Gateway;
use eZ\Publish\Core\FieldType\Relation\RelationStorage\Gateway,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\API\Repository\Values\Content\Relation as APIRelationValue;

/**
 *
 */
class LegacyStorage extends Gateway
{
    const TABLE = "ezcontentobject_link";

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
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     * @throws \RuntimeException if no connection has been set, yet.
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
     * @see \eZ\Publish\SPI\FieldType\Url\UrlStorage\Gateway
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        // insert relation to ezcontentobject_link
        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable( self::TABLE )
        )->set(
            $dbHandler->quoteColumn( "contentclassattribute_id" ),
            $q->bindValue( $field->fieldDefinitionId, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "from_contentobject_id" ),
            $q->bindValue( $versionInfo->contentId, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "from_contentobject_version" ),
            $q->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "op_code" ),
            $q->bindValue( 0, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "relation_type" ),
            $q->bindValue( APIRelationValue::FIELD, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "to_contentobject_id" ),
            $q->bindValue( $field->value->externalData['destinationContentId'], null, \PDO::PARAM_INT )
        );

        $q->prepare()->execute();

        /*if ( ( $row = $this->fetchByLink( $field->value->externalData['destinationContentId'] ) ) !== false )
            $urlId = $row["id"];
        else
            $urlId = $this->insert( $versionInfo, $field );*/

        // Signals that the Value has been modified and that an update is to be performed
        return false;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\Relation\RelationStorage\Gateway
     */
    public function getFieldData( Field $field )
    {
        // @todo This is a bit ugly but it should do for now. A roundtrip to the DB isn't really needed here.
        $field->value->externalData = $field->value->data;
    }

    /**
     * Fetches a row in ezurl table referenced by its $id
     *
     * @param mixed $id
     * @return null|array Hash with columns as keys or null if no entry can be found
     */
    private function fetchById( $id )
    {
        /*$dbHandler = $this->getConnection();

        $q = $dbHandler->createSelectQuery();
        $e = $q->expr;
        $q->select( "*" )
            ->from( $dbHandler->quoteTable( self::TABLE ) )
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

        return false;*/
        throw new \Exception( __METHOD__ . " not implemented yet " );

    }

    /**
     * Fetches a row in ezurl table referenced by $link
     *
     * @param string $link
     * @return null|array Hash with columns as keys or null if no entry can be found
     */
    private function fetchByLink( $link )
    {
        /*$dbHandler = $this->getConnection();

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

        return false;*/
        throw new \Exception( __METHOD__ . " not implemented yet " );
    }

    /**
     * Inserts a new entry in ezurl table with $field value data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @return mixed
     */
    private function insert( VersionInfo $versionInfo, Field $field )
    {
        /*$dbHandler = $this->getConnection();

        $time = time();

        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable( self::TABLE )
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
        );*/
        throw new \Exception( __METHOD__ . " not implemented yet " );
    }
}
