<?php
/**
 * File containing the XmlText LegacyStorage class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\XmlTextStorage\Gateway;

use eZ\Publish\Core\FieldType\XmlText\XmlTextStorage\Gateway;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage as UrlStorage;
use DOMDocument;

class LegacyStorage extends Gateway
{
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
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler )
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
     * Populates $field->value with external data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData( Field $field )
    {
        if ( !$field->value->data instanceof DOMDocument )
            return;

        /** @var $linkTagsById \DOMElement[] */
        $linkIds = array();
        $linkTags = $field->value->data->getElementsByTagName( 'link' );
        if ( $linkTags->length > 0 )
        {
            foreach ( $linkTags as $link )
            {
                $urlId = $link->getAttribute( 'url_id' );
                if ( !empty( $urlId ) )
                    $linkIds[$urlId] = true;
            }

            if ( !empty( $linkIds ) )
            {
                $linkIdUrlMap = $this->getLinksUrl( array_keys( $linkIds ) );
                foreach ( $linkTags as $link )
                {
                    $urlId = $link->getAttribute( 'url_id' );
                    if ( !empty( $urlId ) )
                    {
                        $link->setAttribute( 'url', $linkIdUrlMap[$urlId] );
                        $link->removeAttribute( 'url_id' );
                    }
                }
            }
        }
    }

    /**
     * Fetches rows in ezurl table referenced by IDs in $linkIds set.
     * Returns as hash with URL id as key and corresponding URL as value.
     *
     * @param array $linkIds Set of link Ids
     *
     * @return array
     */
    protected function getLinksUrl( array $linkIds )
    {
        /** @var $q \ezcQuerySelect */
        $q = $this->getConnection()->createSelectQuery();
        $q
            ->select( "id", "url" )
            ->from( UrlStorage::URL_TABLE )
            ->where( $q->expr->in( 'id', $linkIds ) );

        $statement = $q->prepare();
        $statement->execute();
        $linkUrls = array();
        foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row )
        {
            $linkUrls[$row['id']] = $row['url'];
        }

        return $linkUrls;
    }

    /**
     * Stores data, external to XMLText type
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return boolean
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        if ( !$field->value->data instanceof DOMDocument )
            return;

        // Get all element tag types that contain url's or object_remote_id
        $urls = array();
        $remoteIds = array();
        $elements = array();
        foreach ( array( 'link', 'embed', 'embed-inline' ) as $tagName )
        {
            $tags = $field->value->data->getElementsByTagName( $tagName );
            if ( $tags->length === 0 )
                continue;

            // First loop on $elements to populate $urls & $remoteIds
            /** @var $tag \DOMElement */
            foreach ( $tags as $tag )
            {
                $url = null;
                if ( $tag->hasAttribute( 'url' ) )
                    $url = $tag->getAttribute( 'url' );
                else if ( $tag->hasAttribute( 'href' ) )
                    $url = $tag->getAttribute( 'href' );
                else if ( $tag->hasAttribute( 'object_remote_id' ) )
                    $remoteIds[$tag->getAttribute( 'object_remote_id' )] = true;
                else
                    continue;

                // Keep url unique if it has value
                if ( $url )
                    $urls[$url] = true;

                $elements[] = $tag;
            }
        }
        unset( $tags );

        // If we found some elements, fix them to point to internal ids
        if ( !empty( $elements ) )
        {
            $linksIds = $this->getLinksId( array_keys( $urls ) );
            $objectRemoteIdMap = $this->getObjectId( array_keys( $remoteIds ) );

            // Now loop again to insert the right value in "url_id" attribute and fix "object_remote_id"
            /** @var $element \DOMElement */
            foreach ( $elements as $element )
            {
                if ( $element->hasAttribute( 'url' ) )
                {
                    $url = $element->getAttribute( 'url' );
                    if ( !$url )
                        throw new NotFoundException( '<link url=', $url );

                    // Insert url once if not already existing
                    if ( !isset( $linksIds[$url] ) )
                        $linksIds[$url] = $this->insertLink( $url );

                    $element->setAttribute( 'url_id', $linksIds[$url] );
                    $element->removeAttribute( 'url' );
                }
                else if ( $element->hasAttribute( 'href' ) )
                {
                    $url = $element->getAttribute( 'href' );
                    if ( !$url )
                        throw new NotFoundException( '<link href=', $url );

                    // Insert url once if not already existing
                    if ( !isset( $linksIds[$url] ) )
                        $linksIds[$url] = $this->insertLink( $url );

                    $element->setAttribute( 'url_id', $linksIds[$url] );
                    $element->removeAttribute( 'href' );
                }
                else if ( $element->hasAttribute( 'object_remote_id' ) )
                {
                    $objectRemoteId = $element->getAttribute( 'object_remote_id' );
                    if ( !isset( $objectRemoteIdMap[$objectRemoteId] ) )
                        throw new NotFoundException( 'object_remote_id', $objectRemoteId );

                    $element->setAttribute( 'object_id', $objectRemoteIdMap[$objectRemoteId] );
                    $element->removeAttribute( 'object_remote_id' );
                }
            }
        }

        // Return true if some elements where changed
        return !empty( $elements );
    }

    /**
     * Fetches rows in ezurl table referenced by URLs in $linksUrls array.
     * Returns as hash with URL as key and corresponding URL id as value.
     *
     * @param array $linksUrls
     *
     * @return array
     */
    protected function getLinksId( array $linksUrls )
    {
        $linkIds = array();

        if ( !empty( $linksUrls ) )
        {
            /** @var $q \ezcQuerySelect */
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
     * Fetches rows in ezcontentobject table referenced by remoteIds in $linksRemoteIds array.
     * Returns as hash with remote id as key and corresponding id as value.
     *
     * @param array $linksRemoteIds
     *
     * @return array
     */
    protected function getObjectId( array $linksRemoteIds )
    {
        $objectRemoteIdMap = array();

        if ( !empty( $linksRemoteIds ) )
        {
            /** @var $q \ezcQuerySelect */
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
     * Inserts a new entry in ezurl table and returns the table last insert id
     *
     * @param string $url The URL to insert in the database
     */
    protected function insertLink( $url )
    {
        $time = time();
        $dbHandler = $this->getConnection();

        /** @var $q \ezcQueryInsert */
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
