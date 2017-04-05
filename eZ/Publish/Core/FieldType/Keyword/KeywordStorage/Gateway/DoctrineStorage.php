<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use RuntimeException;

class DoctrineStorage extends Gateway
{
    const KEYWORD_TABLE = 'ezkeyword';
    const KEYWORD_ATTRIBUTE_LINK_TABLE = 'ezkeyword_attribute_link';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Stores the keyword list from $field->value->externalData.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field
     * @param int $contentTypeId
     */
    public function storeFieldData(Field $field, $contentTypeId)
    {
        if (empty($field->value->externalData) && !empty($field->id)) {
            $this->deleteFieldData($field->id, $field->versionNo);

            return;
        }

        $existingKeywordMap = $this->getExistingKeywords(
            $field->value->externalData,
            $contentTypeId
        );

        $this->deleteOldKeywordAssignments($field->id, $field->versionNo);

        $this->assignKeywords(
            $field->id,
            $this->insertKeywords(
                array_diff_key(
                    array_fill_keys($field->value->externalData, true),
                    $existingKeywordMap
                ),
                $contentTypeId
            ) + $existingKeywordMap,
            $field->versionNo
        );

        $this->deleteOrphanedKeywords();
    }

    /**
     * Sets the list of assigned keywords into $field->value->externalData.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData(Field $field)
    {
        $field->value->externalData = $this->getAssignedKeywords($field->id, $field->versionNo);
    }

    /**
     * Retrieve the ContentType ID for the given $field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return int
     */
    public function getContentTypeId(Field $field)
    {
        return $this->loadContentTypeId($field->fieldDefinitionId);
    }

    /**
     * Deletes keyword data for the given $fieldId.
     *
     * @param int $fieldId
     * @param int $versionNo
     */
    public function deleteFieldData($fieldId, $versionNo)
    {
        $this->deleteOldKeywordAssignments($fieldId, $versionNo);
        $this->deleteOrphanedKeywords();
    }

    /**
     * Returns a list of keywords assigned to $fieldId.
     *
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return string[]
     */
    protected function getAssignedKeywords($fieldId, $versionNo)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->connection->quoteIdentifier('keyword'))
            ->from($this->connection->quoteIdentifier(self::KEYWORD_TABLE), 'kwd')
            ->innerJoin(
                'kwd',
                $this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE),
                'attr',
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('kwd.id'),
                    $this->connection->quoteIdentifier('attr.keyword_id')
                )
            )
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq(
                        $this->connection->quoteIdentifier('attr.objectattribute_id'),
                        ':fieldId'
                    ),
                    $query->expr()->eq(
                        $this->connection->quoteIdentifier('attr.version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldId', $fieldId)
            ->setParameter(':versionNo', $versionNo)
        ;

        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Retrieves the content type ID for the given $fieldDefinitionId.
     *
     * @param int $fieldDefinitionId
     *
     * @return int
     */
    protected function loadContentTypeId($fieldDefinitionId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->connection->quoteIdentifier('contentclass_id'))
            ->from($this->connection->quoteIdentifier('ezcontentclass_attribute'))
            ->where(
                $query->expr()->eq('id', ':fieldDefinitionId')
            )
            ->setParameter(':fieldDefinitionId', $fieldDefinitionId);

        $statement = $query->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RuntimeException(
                sprintf(
                    'Content Type ID cannot be retrieved based on the field definition ID "%s"',
                    $fieldDefinitionId
                )
            );
        }

        return intval($row['contentclass_id']);
    }

    /**
     * Returns already existing keywords from $keywordList as a map.
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
     * @param int $contentTypeId
     *
     * @return int[]
     */
    protected function getExistingKeywords($keywordList, $contentTypeId)
    {
        // Retrieving potentially existing keywords
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('id'),
                $this->connection->quoteIdentifier('keyword')
            )
            ->from($this->connection->quoteIdentifier(self::KEYWORD_TABLE))
            ->where(
                $query->expr()->andX(
                    $query->expr()->in(
                        $this->connection->quoteIdentifier('keyword'),
                        ':keywordList'
                    ),
                    $query->expr()->eq(
                        $this->connection->quoteIdentifier('class_id'),
                        ':contentTypeId'
                    )
                )
            )
            ->setParameter(':keywordList', $keywordList, Connection::PARAM_STR_ARRAY)
            ->setParameter(':contentTypeId', $contentTypeId);

        $statement = $query->execute();

        $existingKeywordMap = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // filter out keywords that aren't the exact match (e.g. differ by case)
            if (!in_array($row['keyword'], $keywordList)) {
                continue;
            }
            $existingKeywordMap[$row['keyword']] = $row['id'];
        }

        return $existingKeywordMap;
    }

    /**
     * Inserts $keywordsToInsert for $fieldDefinitionId and returns a map of
     * these keywords to their ID.
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
     * @param int $contentTypeId
     *
     * @return int[]
     */
    protected function insertKeywords(array $keywordsToInsert, $contentTypeId)
    {
        $keywordIdMap = [];
        // Inserting keywords not yet registered
        if (!empty($keywordsToInsert)) {
            $insertQuery = $this->connection->createQueryBuilder();
            $insertQuery
                ->insert($this->connection->quoteIdentifier(self::KEYWORD_TABLE))
                ->values(
                    [
                        $this->connection->quoteIdentifier('class_id') => ':contentTypeId',
                        $this->connection->quoteIdentifier('keyword') => ':keyword',
                    ]
                )
                ->setParameter(':contentTypeId', $contentTypeId, \PDO::PARAM_INT);

            foreach (array_keys($keywordsToInsert) as $keyword) {
                $insertQuery->setParameter(':keyword', $keyword);
                $insertQuery->execute();
                $keywordIdMap[$keyword] = $this->connection->lastInsertId(
                    $this->getSequenceName(self::KEYWORD_TABLE, 'id')
                );
            }
        }

        return $keywordIdMap;
    }

    protected function deleteOldKeywordAssignments($fieldId, $versionNo)
    {
        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
            ->where(
                $deleteQuery->expr()->andX(
                    $deleteQuery->expr()->eq(
                        $this->connection->quoteIdentifier('objectattribute_id'),
                        ':fieldId'
                    ),
                    $deleteQuery->expr()->eq(
                        $this->connection->quoteIdentifier('version'),
                        ':versionNo'
                    )
                )
            )
            ->setParameter(':fieldId', $fieldId, \PDO::PARAM_INT)
            ->setParameter(':versionNo', $versionNo, \PDO::PARAM_INT)
        ;

        $deleteQuery->execute();
    }

    /**
     * Assigns keywords from $keywordMap to the field with $fieldId.
     *
     * $keywordMap has the format:
     * <code>
     *  array(
     *      '<keyword>' => <id>,
     *      // ...
     *  );
     * </code>
     *
     * @param int $fieldId
     * @param int[] $keywordMap
     * @param int $versionNo
     */
    protected function assignKeywords($fieldId, array $keywordMap, $versionNo)
    {
        $insertQuery = $this->connection->createQueryBuilder();
        $insertQuery
            ->insert($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
            ->values(
                [
                    $this->connection->quoteIdentifier('keyword_id') => ':keywordId',
                    $this->connection->quoteIdentifier('objectattribute_id') => ':fieldId',
                    $this->connection->quoteIdentifier('version') => ':versionNo',
                ]
            )
        ;

        foreach ($keywordMap as $keyword => $keywordId) {
            $insertQuery
                ->setParameter(':keywordId', $keywordId, \PDO::PARAM_INT)
                ->setParameter(':fieldId', $fieldId, \PDO::PARAM_INT)
                ->setParameter(':versionNo', $versionNo, \PDO::PARAM_INT)
            ;
            $insertQuery->execute();
        }
    }

    /**
     * Deletes all orphaned keywords.
     *
     * Keyword is orphaned if it is not linked to a content attribute through
     * ezkeyword_attribute_link table.
     */
    protected function deleteOrphanedKeywords()
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->connection->quoteIdentifier('kwd.id'))
            ->from($this->connection->quoteIdentifier(self::KEYWORD_TABLE), 'kwd')
            ->leftJoin(
                'kwd',
                $this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE),
                'attr',
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('attr.keyword_id'),
                    $this->connection->quoteIdentifier('kwd.id')
                )
            )
            ->where($query->expr()->isNull('attr.id'));

        $statement = $query->execute();
        $ids = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($ids)) {
            return;
        }

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->connection->quoteIdentifier(self::KEYWORD_TABLE))
            ->where(
                $deleteQuery->expr()->in($this->connection->quoteIdentifier('id'), ':ids')
            )
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $deleteQuery->execute();
    }
}
