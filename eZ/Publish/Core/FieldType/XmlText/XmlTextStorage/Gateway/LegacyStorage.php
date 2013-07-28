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
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
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

        $links = $xpath->query( $xpathExpression );

        if ( empty( $links ) )
        {
            return;
        }

        $linkIdSet = array();
        $linksInfo = array();

        /** @var \DOMElement $link */
        foreach ( $links as $index => $link )
        {
            preg_match(
                "~^ezurl://([^#]*)?(#.*|\\s*)?$~",
                $link->getAttribute( "xlink:href" ),
                $matches
            );
            $linksInfo[$index] = $matches;

            if ( !empty( $matches[1] ) )
            {
                $linkIdSet[$matches[1]] = true;
            }
        }

        $linkUrls = $this->getLinkUrls( array_keys( $linkIdSet ) );

        foreach ( $links as $index => $link )
        {
            list( , $urlId, $fragment ) = $linksInfo[$index];

            if ( isset( $linkUrls[$urlId] ) )
            {
                $href = $linkUrls[$urlId] . $fragment;
            }
            else
            {
                // URL id is empty or not in the DB
                // @TODO log error
                $href = "#";
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
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
        // This will select only links with non-empty 'xlink:href' attribute value
        $xpathExpression = "//docbook:link[string( @xlink:href ) and not( starts-with( @xlink:href, 'ezurl://' )" .
            "or starts-with( @xlink:href, 'ezcontent://' )" .
            "or starts-with( @xlink:href, 'ezlocation://' )" .
            "or starts-with( @xlink:href, '#' ) )]";

        $links = $xpath->query( $xpathExpression );

        if ( empty( $links ) )
        {
            return false;
        }

        $urlSet = array();
        $remoteIdSet = array();
        $linksInfo = array();

        /** @var \DOMElement $link */
        foreach ( $links as $index => $link )
        {
            preg_match(
                "~^(ezremote://)?([^#]*)?(#.*|\\s*)?$~",
                $link->getAttribute( "xlink:href" ),
                $matches
            );
            $linksInfo[$index] = $matches;

            if ( empty( $matches[1] ) )
            {
                $urlSet[$matches[2]] = true;
            }
            else
            {
                $remoteIdSet[$matches[2]] = true;
            }
        }

        $linksIds = $this->getLinkIds( array_keys( $urlSet ) );
        $contentIds = $this->getContentIds( array_keys( $remoteIdSet ) );

        foreach ( $links as $index => $link )
        {
            list( , $protocol, $url, $fragment ) = $linksInfo[$index];

            if ( empty( $protocol ) )
            {
                if ( !isset( $linksIds[$url] ) )
                {
                    $linksIds[$url] = $this->insertLink( $url );
                }
                $href = "ezurl://{$linksIds[$url]}{$fragment}";
            }
            else
            {
                if ( !isset( $contentIds[$url] ) )
                {
                    throw new NotFoundException( "Content", $url );
                }
                $href = "ezcontent://{$contentIds[$url]}{$fragment}";
            }

            $link->setAttribute( "xlink:href", $href );
        }

        return true;
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
     * Fetches rows in ezcontentobject table referenced by remoteIds in $linksRemoteIds array.
     * Returns as hash with remote id as key and corresponding id as value.
     *
     * @param array $linksRemoteIds
     *
     * @return array
     */
    protected function getContentIds( array $linksRemoteIds )
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
