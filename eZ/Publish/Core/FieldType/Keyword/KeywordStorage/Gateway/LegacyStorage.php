<?php

namespace eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway;
use eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway;

class LegacyStorage extends Gateway
{
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
     * Stores the keyword list from $field->value->externalData
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field
     * @param mixed $contentTypeID
     */
    public function storeFieldData( Field $field, $contentTypeID )
    {
        $existingKeywordMap = $this->getExistingKeywords( $field->value->externalData, $field->fieldDefinitionId );

        $keywordsToInsert = $this->getKeywordsToInsert( $field->value->externalData, $existingKeywordMap );

        $insertedKeywordMap = $this->insertKeywords( $keywordsToInsert, $field->fieldDefinitionId );

        $keywordsToAssignMap = array_merge( $existingKeywordMap, $insertedKeywordMap );

        $this->assignKeywords( $field->id, $keywordsToAssignMap );
    }

    /**
     * Sets the list of assigned keywords into $field->value->externalData
     *
     * @param Field $field
     * @return void
     */
    public function getFieldData( Field $field )
    {
        $field->value->externalData = $this->getAssignedKeywords( $field->id );
    }

    /**
     * Returns a list of keywords assigned to $fieldId
     *
     * @param mixed $fieldId
     * @return string[]
     */
    protected function getAssignedKeywords( $fieldId )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select( "keyword" )
            ->from( $dbHandler->quoteTable( "ezkeyword" ) )
            ->innerJoin(
                $dbHandler->quoteTable( "ezkeyword_attribute_link" ),
                $query->expr->eq(
                    $dbHandler->quoteColumn( "id", "ezkeyword" ),
                    $dbHandler->quoteColumn( "keyword_id", "ezkeyword_attribute_link" )
                )
            )
            ->where(
                $query->expr->eq(
                    $dbHandler->quoteColumn( "objectattribute_id", "ezkeyword_attribute_link" ),
                    $field->id
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( PDO::FETCH_COLUMN, 0 );
    }


    /**
     * Retrieves the content type ID for the given $fieldDefinitionId
     *
     * @param mixed $fieldDefinitionId
     * @return void
     * @TODO This should actually be done through SPI in order to allow caching
     */
    protected function getContentTypeId( $fieldDefinitionId )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select( 'contentclass_id' )
            ->from( $dbHandler->quoteTable( 'ezcontentclass_attribute' ) )
            ->where(
                $query->expr->eq( 'id', $field->fieldDefinitionId )
            );

        $statement = $query->prepare();
        $statement->execute();

        $row = $statement->fetch( PDO::FETCH_ASSOC );

        if ( $row === false )
            throw new Logic( 'Content Type ID cannot be retrieved based on the field definition ID' );

        return $row['contentclass_id'];
    }

    /**
     * Returns already existing keywords from $keywordList as a map
     *
     * The map has the following format:
     * <code>
     *  array(
     *      '<keyword>' => <id>,
     *      // ...
     *  );
     * </code>
     *
     * @param string[] $keywordList
     * @param mixed $contentTypeID
     * @return mixed[]
     */
    protected function getExistingKeywords( $keywordList, $contentTypeID )
    {
        $dbHandler = $this->getConnection();

        // Retrieving potentially existing keywords
        $q = $dbHandler->createSelectQuery();
        $q->select( "id", "keyword" )
            ->from( $dbHandler->quoteTable( "ezkeyword" ) )
            ->where(
                $q->expr->lAnd(
                    $q->expr->in(
                        "keyword",
                        $field->value->externalData
                    ),
                    $q->expr->eq( "class_id", $contentTypeID )
                )
            );
        $statement = $q->prepare();
        $statement->execute();

        $existingKeywordMap = array();

        foreach ( $statement->fetchAll( PDO::FETCH_ASSOC ) as $row )
        {
            $existingKeywordMap[$row["keyword"]] = $row["id"];
        }

        return $existingKeywordMap;
    }

    /**
     * Returns a list of keywords to insert.
     *
     * Returns an array in the following format:
     * <code>
     *  array(
     *      '<keyword>' => true,
     *      // ...
     *  );
     * </code>
     *
     * @param mixed[] $existingKeywords
     * @param string[] $keywordList
     * @return mixed[]
     */
    protected function getKeywordsToInsert( $existingKeywords, $keywordList )
    {
        $keywordsToInsert = array_fill_keys( $keywordList, true );

        $keywordIds = array();

        foreach ( $existingKeywords as $keyword => $id )
        {
            unset( $keywordsToInsert[$keyword] );
        }

        return $keywordsToInsert;
    }

    /**
     * Inserts $keywordsToInsert for $fieldDefinitionId and returns a map of
     * these keywords to their ID
     *
     * The returned array has the following format:
     * <code>
     *  array(
     *      '<keyword>' => <id>,
     *      // ...
     *  );
     * </code>
     *
     * @param string[] $keywordsToInsert
     * @param mixed $fieldDefinitionId
     * @return mixed[]
     */
    protected function insertKeywords( array $keywordsToInsert, $fieldDefinitionId )
    {
        $dbHandler = $this->getConnection();

        $keywordIdMap = array();

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

        return $keywordIdMap;
    }

    /**
     * Assignes keywords from $keywordMap to the field with $fieldId
     *
     * $keywordMap has the format:
     * <code>
     *  array(
     *      '<keyword>' => <id>,
     *      // ...
     *  );
     * </code>
     *
     * @param mixed $fieldId
     * @param mixed[] $keywordMap
     * @return void
     */
    protected function assignKeywords( $fieldId, $keywordMap )
    {
        $dbHandler = $this->getConnection();

        $keywordId = null;

        $insertQuery = $dbHandler->createInsertQuery();
        $insertQuery->insertInto(
            $dbHandler->quoteTable( "ezkeyword_attribute_link" )
        )->set(
            $dbHandler->quoteColumn( "keyword_id" ),
            $insertQuery->bindParam( $keywordId )
        )->set(
            $dbHandler->quoteColumn( "objectattribute_id" ),
            $insertQuery->bindValue( $fieldId )
        );

        $statement = $insertQuery->prepare();

        foreach ( $field->value->data as $keyword )
        {
            $keywordId = $keywordsIds[$keyword];
            $statement->execute();
        }
    }
}
