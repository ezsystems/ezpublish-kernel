<?php

namespace eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway;
use eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway,
    eZ\Publish\SPI\Persistence\Content\Field;

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
     * Stores the keyword list from $field->value->externalData
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field
     * @param mixed $contentTypeID
     */
    public function storeFieldData( Field $field, $contentTypeID )
    {
        $existingKeywordMap = $this->getExistingKeywords( $field->value->externalData, $contentTypeID );

        $this->deleteOldKeywordAssignements( $field );

        $this->assignKeywords(
            $field->id,
            $this->insertKeywords(
                array_diff_key(
                    array_fill_keys( $field->value->externalData, true ),
                    $existingKeywordMap
                ),
                $contentTypeID
            ) + $existingKeywordMap
        );
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
     * Retrieve the ContentType ID for the given $field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @return mixed
     */
    public function getContentTypeID( Field $field )
    {
        return $this->loadContentTypeID( $field->fieldDefinitionId );
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
                    $fieldId
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_COLUMN, 0 );
    }


    /**
     * Retrieves the content type ID for the given $fieldDefinitionId
     *
     * @param mixed $fieldDefinitionId
     * @return mixed
     */
    protected function loadContentTypeID( $fieldDefinitionId )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select( 'contentclass_id' )
            ->from( $dbHandler->quoteTable( 'ezcontentclass_attribute' ) )
            ->where(
                $query->expr->eq( 'id', $fieldDefinitionId )
            );

        $statement = $query->prepare();
        $statement->execute();

        $row = $statement->fetch( \PDO::FETCH_ASSOC );

        if ( $row === false )
            throw new \RuntimeException(
                sprintf(
                    'Content Type ID cannot be retrieved based on the field definition ID "%s"',
                    $fieldDefinitionId
                )
            );

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
                        $keywordList
                    ),
                    $q->expr->eq( "class_id", $contentTypeID )
                )
            );
        $statement = $q->prepare();
        $statement->execute();

        $existingKeywordMap = array();

        foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row )
        {
            $existingKeywordMap[$row["keyword"]] = $row["id"];
        }

        return $existingKeywordMap;
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
    protected function insertKeywords( array $keywordsToInsert, $contentTypeID )
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
                $insertQuery->bindValue( $contentTypeID, null, \PDO::PARAM_INT )
            )->set(
                $dbHandler->quoteColumn( "keyword" ),
                $insertQuery->bindParam( $keyword )
            );

            $statement = $insertQuery->prepare();

            foreach ( array_keys( $keywordsToInsert ) as $keyword )
            {
                $statement->execute();
                $keywordIdMap[$keyword] = $dbHandler->lastInsertId(
                    $dbHandler->getSequenceName( 'ezkeyword', 'id' )
                );
            }
            unset( $keyword );
        }

        return $keywordIdMap;
    }

    protected function deleteOldKeywordAssignements( Field $field )
    {
        $dbHandler = $this->getConnection();

        $deleteQuery = $dbHandler->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $dbHandler->quoteTable( "ezkeyword_attribute_link" )
        )->where(
            $deleteQuery->expr->eq(
                $dbHandler->quoteColumn( "objectattribute_id", "ezkeyword_attribute_link" ),
                $deleteQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
            )
        );

        $statement = $deleteQuery->prepare();
        $statement->execute();
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

        foreach ( $keywordMap as $keyword => $keywordId )
        {
            $keywordId = $keywordMap[$keyword];
            $statement->execute();
        }
    }
}
