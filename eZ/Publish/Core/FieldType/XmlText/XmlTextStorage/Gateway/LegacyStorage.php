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
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage as UrlStorage;
use RuntimeException;

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
        if ( !$dbHandler instanceof EzcDbHandler )
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
     * Populates $field->value with external data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData( Field $field )
    {
        $xpath = new \DOMXPath( $field->value->data );
        $xpath->registerNamespace( "docbook", "http://docbook.org/ns/docbook" );
        $xpathExpression = "//docbook:link[starts-with( @xlink:href, 'ezurl://' )]";

        $linksById = array();
        $fragmentsById = array();

        /** @var \DOMElement $link */
        foreach ( $xpath->query( $xpathExpression ) as $link )
        {
            preg_match( "~^ezurl://([^#]*)?(#.*|\\s*)?$~", $link->getAttribute( "xlink:href" ), $matches );
            list( , $urlId, $fragment ) = $matches;

            if ( !empty( $urlId ) )
            {
                $linksById[$urlId] = $link;
                $fragmentsById[$urlId] = $fragment;
            }
        }

        $linkUrls = $this->getLinkUrls( array_keys( $linksById ) );

        foreach ( $linksById as $id => $link )
        {
            if ( isset( $linkUrls[$id] ) )
            {
                $href = $linkUrls[$id] . $fragmentsById[$id];
            }
            else
            {
                // URL entry is missing in DB
                $href = "";
            }

            $link->setAttribute( "xlink:href", $href );
        }
    }

    /**
     * Fetches rows in ezurl table referenced by IDs in $linkIds.
     * Returns as hash with URL id as key and corresponding URL as value.
     *
     * @param array $linkIds Array of link Ids
     *
     * @return array
     */
    private function getLinkUrls( array $linkIds )
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
     * Stores data, external to XMLText type
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return boolean
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        $xpath = new \DOMXPath( $field->value->data );
        $xpath->registerNamespace( "docbook", "http://docbook.org/ns/docbook" );
        $xpathExpression = "//docbook:link[not(" .
            "starts-with( @xlink:href, 'ezurl://' )" .
            "or starts-with( @xlink:href, 'ezcontent://' )" .
            "or starts-with( @xlink:href, 'ezlocation://' )" .
            "or starts-with( @xlink:href, 'ezremote://' )" .
            "or starts-with( @xlink:href, '#' ) )]";

        $linksByUrl = array();
        $fragmentsByUrl = array();

        /** @var \DOMElement $link */
        foreach ( $xpath->query( $xpathExpression ) as $link )
        {
            preg_match( "~^([^#]*)?(#.*|\\s*)?$~", $link->getAttribute( "xlink:href" ), $matches );
            list( , $url, $fragment ) = $matches;

            $linksByUrl[$url] = $link;
            $fragmentsByUrl[$url] = $fragment;
        }

        $linksIds = $this->getLinkIds( array_keys( $linksByUrl ) );

        foreach ( $linksByUrl as $url => $link )
        {
            if ( isset( $linksIds[$url] ) )
            {
                $linkId = $linksIds[$url];
            }
            else
            {
                $linkId = $this->insertLink( $url );
            }

            $link->setAttribute( "xlink:href", "ezurl://{$linkId}{$fragmentsByUrl[$url]}" );
        }

        return !empty( $linksByUrl );
    }

    /**
     * Fetches rows in ezurl table referenced by URLs in $linksUrls array.
     * Returns as hash with URL as key and corresponding URL id as value.
     *
     * @param array $linksUrls
     *
     * @return array
     */
    private function getLinkIds( array $linksUrls )
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
     * Inserts a new entry in ezurl table and returns the table last insert id
     *
     * @param string $url The URL to insert in the database
     *
     * @return mixed
     */
    private function insertLink( $url )
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
