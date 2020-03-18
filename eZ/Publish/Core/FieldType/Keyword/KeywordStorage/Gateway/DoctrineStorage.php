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

    /** @var \Doctrine\DBAL\Connection */
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
     * Deletes keyword data for the given $fieldId and $versionNo.
     *
     * @param int $fieldId
     * @param int $versionNo
     */
    public function deleteFieldData($fieldId, $versionNo)
    {
        $this->deleteCurrentVersionRelations($fieldId, $versionNo);
        $this->deleteOrphanedKeywords();
    }

    /**
     * Deletes current version from keyword <=> field relation.
     *
     * @param int $fieldId
     * @param int $versionNo
     */
    protected function deleteCurrentVersionRelations($fieldId, $versionNo)
    {
        $existingKeywordRelations = $this->getKeywordRelations($fieldId);

        foreach ($existingKeywordRelations as $keywordId => $versions) {
            if (!$versions || !is_array($versions)) {
                // serialization failed, means that relation has been created before EZP-31471,
                // therefore it must be deleted completely
                $this->deleteOldKeywordAssignments($fieldId);

                // we can skipx continuing foreach loop, as we are sure at that point that all keyword assignments
                // will be deleted for current $fieldId
                return;
            }

            if ($versions && is_array($versions) && ($key = array_search($versionNo, $versions)) !== false) {
                // version exists in relation
                unset($versions[$key]);

                if (empty($versions)) {
                    // after deleting last version within this relation, we can safely delete whole relation
                    // (i.e. during trash cleanup)
                    $this->deleteOldKeywordAssignments($fieldId);
                }

                $versions = serialize($versions);

                $query = $this->connection->createQueryBuilder();
                $query
                    ->update($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
                    ->set(
                        'versions',
                        ':versions'
                    )
                    ->where(
                        $query->expr()->andX(
                            $query->expr()->eq(
                                $this->connection->quoteIdentifier('objectattribute_id'),
                                ':objectAttributeId'
                            ),
                            $query->expr()->eq(
                                $this->connection->quoteIdentifier('keyword_id'),
                                ':keywordId'
                            )
                        )
                    )
                    ->setParameter(':objectAttributeId', $fieldId)
                    ->setParameter(':keywordId', $keywordId)
                    ->setParameter(':versions', $versions);

                $query->execute();
            }
        }
    }

    /**
     * Deletes keyword <=> field relation
     * Method will be used when the current relation is created before EZP-31471.
     *
     * @param int $fieldId
     */
    protected function deleteOldKeywordAssignments($fieldId)
    {
        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
            ->where(
                $deleteQuery->expr()->eq(
                    $this->connection->quoteIdentifier('objectattribute_id'),
                    ':fieldId'
                )
            )
            ->setParameter(':fieldId', $fieldId, \PDO::PARAM_INT);

        $deleteQuery->execute();
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
     * Returns a list of keywords assigned to $fieldId.
     *
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return mixed[]
     */
    protected function getAssignedKeywords($fieldId, $versionNo)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->connection->quoteIdentifier('kwd.keyword'),
                $this->connection->quoteIdentifier('attr.versions')
            )
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
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('attr.objectattribute_id'),
                    ':fieldId'
                )
            )
            ->setParameter(':fieldId', $fieldId);

        $statement = $query->execute();
        $records = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($records as $key => $record) {
            $availableVersions = unserialize($record['versions']);
            //added not and empty conditions to avoid bc break for keywords which do not have assigned content versions
            if (!$availableVersions || empty($availableVersions) || in_array($versionNo, $availableVersions)) {
                $records[$key] = $record['keyword'];
            } else {
                unset($records[$key]);
            }
        }

        return $records;
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

        return (int)$row['contentclass_id'];
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
                $keywordIdMap[$keyword] = (int)$this->connection->lastInsertId(
                    $this->getSequenceName(self::KEYWORD_TABLE, 'id')
                );
            }
        }

        return $keywordIdMap;
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
     * @param array $keywordMap
     * @param int|null $versionNo
     */
    protected function assignKeywords($fieldId, array $keywordMap, int $versionNo = null)
    {
        $existingKeywordRelations = $this->getKeywordRelations($fieldId);

        $insertQuery = $this->connection->createQueryBuilder();
        $insertQuery
            ->insert($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
            ->values(
                [
                    $this->connection->quoteIdentifier('keyword_id') => ':keywordId',
                    $this->connection->quoteIdentifier('objectattribute_id') => ':fieldId',
                    $this->connection->quoteIdentifier('versions') => ':versions',
                ]
            );

        $updateQuery = $this->connection->createQueryBuilder();
        $updateQuery
            ->update($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
            ->set(
                'versions',
                ':versions'
            )
            ->where(
                $updateQuery->expr()->andX(
                    $updateQuery->expr()->eq(
                        $this->connection->quoteIdentifier('objectattribute_id'),
                        ':objectAttributeId'
                    ),
                    $updateQuery->expr()->in(
                        $this->connection->quoteIdentifier('keyword_id'),
                        ':keywordId'
                    )
                )
            )
            ->setParameter(':objectAttributeId', $fieldId);

        $keywordsToLink = array_intersect(array_keys($existingKeywordRelations), $keywordMap);
        $keywordsToUnlinkOrCreate = array_merge(
            array_diff($keywordMap, array_keys($existingKeywordRelations)),
            array_diff(array_keys($existingKeywordRelations), $keywordMap)
        );

        // handling versions that need to be removed or additionally added to link
        foreach ($keywordsToUnlinkOrCreate as $keywordId) {
            $versions = in_array($keywordId, array_keys($existingKeywordRelations))
                ? $existingKeywordRelations[$keywordId]
                : [];
            if (($key = array_search($versionNo, $versions)) !== false) {
                // version found, needs to be removed
                unset($versions[$key]);
                $versions = serialize($versions);
                $updateQuery->setParameter(':versions', $versions, \PDO::PARAM_STR);
                $updateQuery->setParameter(':keywordId', $keywordId, \PDO::PARAM_INT);
                $updateQuery->execute();
            } else {
                // version not found, new attribute link with single current version has to be created
                if (in_array($keywordId, $keywordMap)) {
                    $versions[] = $versionNo;
                    $versions = serialize($versions);
                    $insertQuery
                        ->setParameter(':keywordId', $keywordId, \PDO::PARAM_INT)
                        ->setParameter(':fieldId', $fieldId, \PDO::PARAM_INT)
                        ->setParameter(':versions', $versions, \PDO::PARAM_STR);
                    $insertQuery->execute();
                }
            }
        }

        // handling adding current version into existing attribute links
        foreach ($keywordsToLink as $keywordId) {
            $versions = in_array($keywordId, array_keys($existingKeywordRelations))
                ? $existingKeywordRelations[$keywordId]
                : [];
            if (!in_array($versionNo, $versions)) {
                $versions[] = $versionNo;
                $versions = serialize($versions);
                $updateQuery
                    ->setParameter(':versions', $versions, \PDO::PARAM_STR);
                $updateQuery->setParameter(':keywordId', $keywordId, \PDO::PARAM_INT);
                $updateQuery->execute();
            }
        }
    }

    /**
     * Return keyword content relations based on provided $fieldId and $keywordList in format:
     * <code>
     *  array(
     *      '<keywordId>' => <versions>,
     *      // ...
     *  );
     * </code>.
     *
     * @param int $fieldId
     *
     * @return array
     */
    protected function getKeywordRelations($fieldId)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->connection->quoteIdentifier('keyword_id'),
                $this->connection->quoteIdentifier('versions')
            )
            ->from($this->connection->quoteIdentifier(self::KEYWORD_ATTRIBUTE_LINK_TABLE))
            ->where(
                $query->expr()->eq(
                    $this->connection->quoteIdentifier('objectattribute_id'),
                    ':objectAttributeId'
                )
            )
            ->setParameter(':objectAttributeId', $fieldId);

        $statement = $query->execute();
        $records = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        array_walk($records, function (&$versions) {
            $versions = unserialize($versions);
        });

        return $records;
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
