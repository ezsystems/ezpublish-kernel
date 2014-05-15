<?php
/**
 * File containing the RichText LegacyStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;

use eZ\Publish\Core\FieldType\RichText\RichTextStorage\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage as UrlStorage;
use RuntimeException;

class LegacyStorage extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof DatabaseHandler )
        {
            throw new RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
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
     * For given array of URL ids returns a hash of corresponding URLs,
     * with URL ids as keys.
     *
     * Non-existent ids are ignored.
     *
     * @param array $linkIds Array of link Ids
     *
     * @return array
     */
    public function getLinkUrls( array $linkIds )
    {
        $linkUrls = array();

        if ( !empty( $linkIds ) )
        {
            $q = $this->getConnection()->createSelectQuery();
            $q
                ->select( "id", "url" )
                ->from( UrlStorage::URL_TABLE )
                ->where( $q->expr->in( 'id', $linkIds ) );

            $statement = $q->prepare();
            $statement->execute();
            foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row )
            {
                $linkUrls[$row['id']] = $row['url'];
            }
        }

        return $linkUrls;
    }

    /**
     * For given array of URLs returns a hash of corresponding ids,
     * with URLs as keys.
     *
     * Non-existent URLs are ignored.
     *
     * @param array $linksUrls
     *
     * @return array
     */
    public function getLinkIds( array $linksUrls )
    {
        $linkIds = array();

        if ( !empty( $linksUrls ) )
        {
            $q = $this->getConnection()->createSelectQuery();
            $q
                ->select( "id", "url" )
                ->from( UrlStorage::URL_TABLE )
                ->where( $q->expr->in( 'url', $linksUrls ) );

            $statement = $q->prepare();
            $statement->execute();
            foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row )
            {
                $linkIds[$row['url']] = $row['id'];
            }
        }

        return $linkIds;
    }

    /**
     * For given array of Content remote ids returns a hash of corresponding
     * Content ids, with remote ids as keys.
     *
     * Non-existent ids are ignored.
     *
     * @param array $linksRemoteIds
     *
     * @return array
     */
    public function getContentIds( array $linksRemoteIds )
    {
        $objectRemoteIdMap = array();

        if ( !empty( $linksRemoteIds ) )
        {
            $q = $this->getConnection()->createSelectQuery();
            $q
                ->select( "id", "remote_id" )
                ->from( "ezcontentobject" )
                ->where( $q->expr->in( 'remote_id', $linksRemoteIds ) );

            $statement = $q->prepare();
            $statement->execute();
            foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row )
            {
                $objectRemoteIdMap[$row['remote_id']] = $row['id'];
            }
        }

        return $objectRemoteIdMap;
    }

    /**
     * Inserts a new URL and returns its id.
     *
     * @param string $url The URL to insert in the database
     *
     * @return mixed
     */
    public function insertLink( $url )
    {
        $time = time();
        $dbHandler = $this->getConnection();

        $q = $dbHandler->createInsertQuery();
        $q->insertInto(
            $dbHandler->quoteTable( UrlStorage::URL_TABLE )
        )->set(
            $dbHandler->quoteColumn( "created" ),
            $q->bindValue( $time, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "modified" ),
            $q->bindValue( $time, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "original_url_md5" ),
            $q->bindValue( md5( $url ) )
        )->set(
            $dbHandler->quoteColumn( "url" ),
            $q->bindValue( $url )
        );

        $q->prepare()->execute();

        return $dbHandler->lastInsertId(
            $dbHandler->getSequenceName( UrlStorage::URL_TABLE, "id" )
        );
    }
}
