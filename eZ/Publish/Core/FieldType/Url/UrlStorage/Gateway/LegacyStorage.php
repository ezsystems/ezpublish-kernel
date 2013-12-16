<?php
/**
 * File containing the Url LegacyStorage Gateway
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Url field type external storage gateway implementation using Zeta Database Component.
 */
class LegacyStorage extends Gateway
{
    const URL_TABLE = "ezurl";
    const URL_LINK_TABLE = "ezurl_object_link";

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
        if ( ! ( $dbHandler instanceof EzcDbHandler ) )
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
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler|\ezcDbHandler
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
        if ( ( $row = $this->fetchByLink( $field->value->externalData ) ) !== false )
        {
            $urlId = $row["id"];
        }
        else
        {
            $urlId = $this->insert( $field );
        }

        $this->linkUrl( $urlId, $field->id, $versionInfo->versionNo );

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
        // @TODO: maybe log an error if URL entry was not found?
        $field->value->externalData = isset( $url["url"] ) ? $url["url"] : "";
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
     *
     * @return mixed
     */
    private function insert( Field $field )
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

    /**
     * Creates link to URL with $urlId for field with $fieldId in $versionNo.
     *
     * @param mixed $urlId
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return void
     */
    protected function linkUrl( $urlId, $fieldId, $versionNo )
    {
        $dbHandler = $this->getConnection();

        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable( self::URL_LINK_TABLE )
        )->set(
            $dbHandler->quoteColumn( "contentobject_attribute_id" ),
            $q->bindValue( $fieldId, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "contentobject_attribute_version" ),
            $q->bindValue( $versionNo, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "url_id" ),
            $q->bindValue( $urlId, null, \PDO::PARAM_INT )
        );

        $q->prepare()->execute();
    }

    /**
     * Deletes external URL data for field with $fieldId in $versionNo.
     *
     * If URL unlinked is found to be orphaned, it will be deleted.
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return void
     */
    public function deleteFieldData( $fieldId, $versionNo )
    {
        $this->unlinkUrl( $fieldId, $versionNo );
        $this->deleteOrphanedUrls();
    }

    /**
     * Removes link to URL for $fieldId in $versionNo.
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return void
     */
    protected function unlinkUrl( $fieldId, $versionNo )
    {
        $dbHandler = $this->getConnection();

        $deleteQuery = $dbHandler->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $dbHandler->quoteTable( self::URL_LINK_TABLE )
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in( $dbHandler->quoteColumn( "contentobject_attribute_id" ), $fieldId ),
                $deleteQuery->expr->in( $dbHandler->quoteColumn( "contentobject_attribute_version" ), $versionNo )
            )
        );

        $deleteQuery->prepare()->execute();
    }

    /**
     * Deletes all orphaned URLs.
     *
     * @todo using two queries because zeta Database does not support joins in delete query.
     * That could be avoided if the feature is implemented there.
     *
     * URL is orphaned if it is not linked to a content attribute through ezurl_object_link table.
     *
     * @return void
     */
    protected function deleteOrphanedUrls()
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select(
            $dbHandler->quoteColumn( "id", self::URL_TABLE )
        )->from(
            $dbHandler->quoteTable( self::URL_TABLE )
        )->leftJoin(
            $dbHandler->quoteTable( self::URL_LINK_TABLE ),
            $query->expr->eq(
                $dbHandler->quoteColumn( "url_id", self::URL_LINK_TABLE ),
                $dbHandler->quoteColumn( "id", self::URL_TABLE )
            )
        )->where(
            $query->expr->isNull(
                $dbHandler->quoteColumn( "url_id", self::URL_LINK_TABLE )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
        $ids = $statement->fetchAll( \PDO::FETCH_COLUMN );

        if ( empty( $ids ) )
        {
            return;
        }

        $deleteQuery = $dbHandler->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $dbHandler->quoteTable( self::URL_TABLE )
        )->where(
            $deleteQuery->expr->in( $dbHandler->quoteColumn( "id" ), $ids )
        );

        $deleteQuery->prepare()->execute();
    }
}
