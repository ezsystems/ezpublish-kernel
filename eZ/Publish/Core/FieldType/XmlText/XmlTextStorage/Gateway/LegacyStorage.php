<?php
/**
 * File containing the XmlText LegacyStorage class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\XmlTextStorage\Gateway;

use eZ\Publish\Core\FieldType\XmlText\XmlTextStorage\Gateway,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage as UrlStorage,
    DOMDocument;

class LegacyStorage extends Gateway
{
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
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler )
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
     * @return array
     */
    private function getLinksUrl( array $linkIds )
    {
        /** @var $q ezcQuerySelect */
        $q = $this->getConnection()->createSelectQuery();
        $q
            ->select( '*' )
            ->from( UrlStorage::URL_TABLE )
            ->where( $q->expr->in( 'id', $linkIds ) )
        ;

        $statement = $q->prepare();
        $statement->execute();
        $linkUrls = array();
        foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row)
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
     * @return bool
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        // TODO: Implement storeFieldData() method.
    }
}
