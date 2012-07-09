<?php
/**
 * File containing the KeywordStorage Converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\SPI\FieldType\FieldStorage,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\Base\Exceptions\Logic,
    PDO;

/**
 * Converter for Keyword field type external storage
 */
class KeywordStorage implements FieldStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( Field $field, array $context )
    {
        // If there is no keywords, there is nothing to store.
        if ( empty( $field->value->data ) )
            return;

        $dbHandler = $context["connection"];

        // Retrieving the Content Type ID which is required in ezkeyword table
        // but that isn't (and probably shouldn't be) available directly from
        // $field.
        $q = $dbHandler->createSelectQuery();
        $q->select( "contentclass_id" )
            ->from( $dbHandler->quoteTable( "ezcontentclass_attribute" ) )
            ->where(
                $q->expr->eq( "id", $field->fieldDefinitionId )
            );

        $statement = $q->prepare();
        $statement->execute();

        $row = $statement->fetch( PDO::FETCH_ASSOC );

        if ( $row === false )
            throw new Logic( "Content Type ID can't be retrieved based on the field definition ID" );

        $contentTypeID = $row["contentclass_id"];

        // Retrieving potentially existing keywords
        $q = $dbHandler->createSelectQuery();
        $q->select( "id", "keyword" )
            ->from( $dbHandler->quoteTable( "ezkeyword" ) )
            ->where(
                $q->expr->lAnd(
                    $q->expr->in(
                        "keyword",
                        $field->value->data
                    ),
                    $q->expr->eq( "class_id", $contentTypeID )
                )
            );
        $statement = $q->prepare();
        $statement->execute();

        // Hash of keyword IDs, indexed by the keyword
        $keywordsIds = array_fill_keys( $field->value->data, true );
        // Set of keywords that will have to be inserted
        $keywordsToInsert = $keywordsIds;
        foreach ( $statement->fetchAll( PDO::FETCH_ASSOC ) as $row )
        {
            $keywordsIds[$row["keyword"]] = $row["id"];
            unset( $keywordsToInsert[$row["keyword"]] );
        }

        // Inserting keywords not yet registered
        if ( !empty( $keywordsToInsert ) )
        {
            $insertQuery = $dbHandler->createInsertQuery();
            $insertQuery->insertInto(
                $dbHandler->quoteTable( "ezkeyword" )
            )->set(
                $dbHandler->quoteColumn( "class_id" ),
                $insertQuery->bindValue( $contentTypeID, null, PDO::PARAM_INT )
            )->set(
                $dbHandler->quoteColumn( "keyword" ),
                $insertQuery->bindParam( $keyword )
            );

            $statement = $insertQuery->prepare();

            foreach ( array_keys( $keywordsToInsert ) as $keyword )
            {
                $statement->execute();
                $keywordsIds[$keyword] = $dbHandler->lastInsertId();
            }
            unset( $keyword );
        }

        // Linking keywords to the field
        $insertQuery = $dbHandler->createInsertQuery();
        $insertQuery->insertInto(
            $dbHandler->quoteTable( "ezkeyword_attribute_link" )
        )->set(
            $dbHandler->quoteColumn( "keyword_id" ),
            $insertQuery->bindParam( $keywordId )
        )->set(
            $dbHandler->quoteColumn( "objectattribute_id" ),
            $insertQuery->bindValue( $field->id )
        );

        $statement = $insertQuery->prepare();

        foreach ( $field->value->data as $keyword ) {
            $keywordId = $keywordsIds[$keyword];
            $statement->execute();
        }
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\Repository\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\Repository\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( Field $field, array $context )
    {
        $dbHandler = $context["connection"];

        $q = $dbHandler->createSelectQuery();
        $q->select( "keyword" )
            ->from( $dbHandler->quoteTable( "ezkeyword" ) )
            ->innerJoin(
                $dbHandler->quoteTable( "ezkeyword_attribute_link" ),
                $q->expr->eq(
                    $dbHandler->quoteColumn( "id", "ezkeyword" ),
                    $dbHandler->quoteColumn( "keyword_id", "ezkeyword_attribute_link" )
                )
            )
            ->where(
                $q->expr->eq(
                    $dbHandler->quoteColumn( "objectattribute_id", "ezkeyword_attribute_link" ),
                    $field->id
                )
            );

        $statement = $q->prepare();
        $statement->execute();

        $keywords = $statement->fetchAll( PDO::FETCH_COLUMN, 0 );
        if ( $keywords === false )
            throw new Logic( "Fetching keywords data failed" );

        $field->value->data = $keywords;
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
    public function copyFieldData( Field $field, array $context )
    {
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( Field $field, array $context )
    {
    }
}
