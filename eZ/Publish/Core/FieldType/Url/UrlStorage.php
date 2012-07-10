<?php
/**
 * File containing the UrlStorage Converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Media;
use eZ\Publish\SPI\FieldType\FieldStorage,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\Core\FieldType\Url\Value as UrlValue;

/**
 * Converter for Url field type external storage
 */
class UrlStorage implements FieldStorage
{
    const URL_TABLE = "ezurl";

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $dbHandler = $context["connection"];
        if ( ( $row = $this->fetchByLink( $field->value->externalData, $dbHandler ) ) !== false )
            $urlId = $row["id"];
        else
            $urlId = $this->insert( $field->value->externalData, $dbHandler );

        $field->value->data["urlId"] = $urlId;

        // Signals that the Value has been modified and that an update is to be performed
        return true;
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $url = $this->fetchById( $field->value->data["urlId"], $context["connection"] );

        $field->value->externalData = $url["link"];
    }

    /**
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( array $fieldId, array $context )
    {
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function copyFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
    }

    /**
     * Fetches a row in ezurl table referenced by its $id
     *
     * @param mixed $id
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @return null|array Hash with columns as keys or null if no entry can be found
     */
    private function fetchById( $id, EzcDbHandler $dbHandler )
    {
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
        if ( !empty( $rows ) )
        {
            return $rows[0];
        }

        return null;
    }

    /**
     * Fetches a row in ezurl table referenced by $link
     *
     * @param string $link
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @return null|array Hash with columns as keys or null if no entry can be found
     */
    private function fetchByLink( $link, EzcDbHandler $dbHandler )
    {
        $q = $dbHandler->createSelectQuery();
        $e = $q->expr;
        $q->select( "*" )
            ->from( $dbHandler->quoteTable( self::URL_TABLE ) )
            ->where(
                $e->eq( "link", $q->bindValue( $link ) )
            );
        $statement = $q->prepare();
        $statement->execute();
        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( !empty( $rows ) )
        {
            return $rows[0];
        }

        return null;
    }

    /**
     * Inserts a new entry in ezurl table with $field value data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @return mixed
     */
    private function insert( VersionInfo $versionInfo, Field $field, EzcDbHandler $dbHandler )
    {
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
}
