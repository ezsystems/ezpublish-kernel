<?php
/**
 * File containing the Url class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Fields\Storage,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Content\FieldType\Url\Value as UrlValue,
    ezp\Io\ContentType;

/**
 * Converter for Url field type external storage
 */
class UrlStorage implements Storage
{
    const URL_TABLE = "ezurl";

    /**
     * @see \ezp\Persistence\Fields\Storage
     */
    public function storeFieldData( Field $field, array $context )
    {
        $dbHandler = $context["connection"];
        if ( ( $row = $this->fetchByLink( $field->value->data->link, $dbHandler ) ) !== false )
            $urlId = $row["id"];
        else
            $urlId = $this->insert( $field->value->data->link, $dbHandler );

        $q = $dbHandler->createUpdateQuery();

        $q->update(
            $dbHandler->quoteTable( "ezcontentobject_attribute" )
        )->set(
            $dbHandler->quoteColumn( "data_int" ),
            $q->bindValue( $urlId, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->eq( $dbHandler->quoteColumn( "id" ), $field->id )
        );

        $stmt = $q->prepare();
        $stmt->execute();
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link ezp\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link ezp\Content\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link ezp\Content\FieldType\TextLine\Value} object).
     *
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( Field $field, array $context )
    {
        $url = $this->fetchById( $field->value->data->getState( "urlId" ), $context["connection"] );

        $field->value->data->link = $url["link"];
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
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     */
    public function copyFieldData( Field $field, array $context )
    {
    }

    /**
     * @param \ezp\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( Field $field, array $context )
    {
    }

    /**
     * Fetches a row in ezurl table referenced by its $id
     *
     * @param mixed $id
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
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
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
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
     * @param \ezp\Persistence\Content\Field $field
     * @param \ezp\Persistence\Storage\Legacy\EzcDbHandler $dbHandler
     * @return mixed
     */
    private function insert( Field $field, EzcDbHandler $dbHandler )
    {
        $data = $field->value->data;
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
            $q->bindValue( md5( $data->link ) )
        )->set(
            $dbHandler->quoteColumn( "url" ),
            $q->bindValue( $data->link )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $dbHandler->lastInsertId(
            $dbHandler->getSequenceName( self::URL_TABLE, "id" )
        );
    }
}
