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
        $linkTagsById = array();
        $linkIds = array();
        $linkTags = $field->value->data->getElementsByTagName( 'link' );
        if ( $linkTags->length > 0 )
        {
            foreach ( $linkTags as $link )
            {
                $urlId = $link->getAttribute( 'url_id' );
                $linkIds[] = $urlId;
                $linkTagsById[$urlId] = $link;
            }

            foreach ( $this->getLinksUrl( $linkIds ) as $id => $url )
            {
                $linkTagsById[$id]->setAttribute( 'url', $url );
            }
        }
    }

    /**
     * Fetches rows in ezurl table referenced by IDs in $linkIds array.
     * Returns as hash with URL id as key and corresponding URL as value.
     *
     * @param array $linkIds
     *
     * @return array
     */
    private function getLinksUrl( array $linkIds )
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

        $linksUrls = array();
        $linkTagsByUrl = array();
        $linkTags = $field->value->data->getElementsByTagName( 'link' );
        if ( $linkTags->length > 0 )
        {
            // First loop on $linkTags to populate $linksUrls
            /** @var $link \DOMElement */
            foreach ( $linkTags as $link )
            {
                if ( $link->hasAttribute( 'url' ) )
                    $url = $link->getAttribute( 'url' );
                else if ( $link->hasAttribute( 'href' ) )
                    $url = $link->getAttribute( 'href' );
                else
                    continue;

                $linksUrls[] = $url;
                $linkTagsByUrl[$url] = $link;
            }

            $linksIds = $this->getLinksId( $linksUrls );

            // Now loop against $linkTagsByUrl to insert the right value in "url_id" attribute
            /** @var $link \DOMElement */
            foreach ( $linkTagsByUrl as $url => $link )
            {
                if ( isset( $linksIds[$url] ) )
                    $linkId = $linksIds[$url];
                else
                    $linkId = $this->insertLink( $url );

                $link->setAttribute( 'url_id', $linkId );
                $link->removeAttribute( 'url' );
                $link->removeAttribute( 'href' );
            }
        }
    }

    /**
     * Fetches rows in ezurl table referenced by URLs in $linksUrls array.
     * Returns as hash with URL as key and corresponding URL id as value.
     *
     * @param array $linksUrls
     *
     * @return array
     */
    private function getLinksId( array $linksUrls )
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
     * Inserts a new entry in ezurl table and returns the table last insert id
     *
     * @param string $url The URL to insert in the database
     */
    private function insertLink( $url )
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
            $dbHandler->getSequenceName( self::URL_TABLE, "id" )
        );
    }
}
