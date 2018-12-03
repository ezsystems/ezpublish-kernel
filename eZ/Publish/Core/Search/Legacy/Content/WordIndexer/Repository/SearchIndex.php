<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\WordIndexer\Repository;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use PDO;

/**
 * A service encapsulating database operations on ezsearch* tables.
 */
class SearchIndex
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * SearchIndex constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    public function __construct(
        DatabaseHandler $dbHandler
    ) {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Fetch already indexed words from database (legacy db table: ezsearch_word).
     *
     * @param array $words
     *
     * @return array
     */
    public function getWords(array $words)
    {
        $query = $this->dbHandler->createSelectQuery();

        // use array_map as some DBMS-es do not cast integers to strings by default
        $query->select('*')
            ->from('ezsearch_word')
            ->where($query->expr->in('word', array_map('strval', $words)));

        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Increase the object count of the given words by one.
     *
     * @param array $wordId
     */
    public function incrementWordObjectCount(array $wordId)
    {
        $this->updateWordObjectCount($wordId, ['object_count' => 'object_count + 1']);
    }

    /**
     * Decrease the object count of the given words by one.
     *
     * @param array $wordId
     */
    public function decrementWordObjectCount(array $wordId)
    {
        $this->updateWordObjectCount($wordId, ['object_count' => 'object_count - 1']);
    }

    /**
     * Insert new words (legacy db table: ezsearch_word).
     *
     * @param array $words
     */
    public function addWords(array $words)
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto('ezsearch_word');

        $word = null;

        $query->set(
            'word',
            ':word'
        )->set(
            'object_count',
            '1'
        );
        $stmt = $query->prepare();
        foreach ($words as $word) {
            $stmt->execute(['word' => $word]);
        }
    }

    /**
     * Remove entire search index.
     */
    public function purge()
    {
        $this->dbHandler->beginTransaction();
        $query = $this->dbHandler->createDeleteQuery();
        $tables = [
            'ezsearch_object_word_link',
            'ezsearch_search_phrase',
            'ezsearch_word',
        ];
        foreach ($tables as $tbl) {
            $query->deleteFrom($tbl);
            $stmt = $query->prepare();
            $stmt->execute();
        }
        $this->dbHandler->commit();
    }

    /**
     * Link word with specific content object (legacy db table: ezsearch_object_word_link).
     *
     * @param $wordId
     * @param $contentId
     * @param $frequency
     * @param $placement
     * @param $nextWordId
     * @param $prevWordId
     * @param $contentTypeId
     * @param $fieldTypeId
     * @param $published
     * @param $sectionId
     * @param $identifier
     * @param $integerValue
     */
    public function addObjectWordLink($wordId,
                                      $contentId,
                                      $frequency,
                                      $placement,
                                      $nextWordId,
                                      $prevWordId,
                                      $contentTypeId,
                                      $fieldTypeId,
                                      $published,
                                      $sectionId,
                                      $identifier,
                                      $integerValue
    ) {
        $assoc = [
            'word_id' => $wordId,
            'contentobject_id' => $contentId,
            'frequency' => $frequency,
            'placement' => $placement,
            'next_word_id' => $nextWordId,
            'prev_word_id' => $prevWordId,
            'contentclass_id' => $contentTypeId,
            'contentclass_attribute_id' => $fieldTypeId,
            'published' => $published,
            'section_id' => $sectionId,
            'identifier' => $identifier,
            'integer_value' => $integerValue,
        ];
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto('ezsearch_object_word_link');
        foreach ($assoc as $column => $value) {
            $query->set($this->dbHandler->quoteColumn($column), $query->bindValue($value));
        }
        $stmt = $query->prepare();
        $stmt->execute();
    }

    /**
     * Get all words related to the content object (legacy db table: ezsearch_object_word_link).
     *
     * @param $contentId
     *
     * @return array
     */
    public function getContentObjectWords($contentId)
    {
        $query = $this->dbHandler->createSelectQuery();

        $this->setContentObjectWordsSelectQuery($query);

        $stmt = $query->prepare();
        $stmt->execute(['contentId' => $contentId]);

        $wordIDList = [];

        while (false !== ($row = $stmt->fetch(PDO::FETCH_NUM))) {
            $wordIDList[] = $row[0];
        }

        return $wordIDList;
    }

    /**
     * Delete words not related to any content object.
     */
    public function deleteWordsWithoutObjects()
    {
        $query = $this->dbHandler->createDeleteQuery();

        $query->deleteFrom($this->dbHandler->quoteTable('ezsearch_word'))
            ->where($query->expr->eq($this->dbHandler->quoteColumn('object_count'), ':c'));

        $stmt = $query->prepare();
        $stmt->execute(['c' => 0]);
    }

    /**
     * Delete relation between a word and a content object.
     *
     * @param $contentId
     */
    public function deleteObjectWordsLink($contentId)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom($this->dbHandler->quoteTable('ezsearch_object_word_link'))
            ->where($query->expr->eq($this->dbHandler->quoteColumn('contentobject_id'), ':contentId'));

        $stmt = $query->prepare();
        $stmt->execute(['contentId' => $contentId]);
    }

    /**
     * Set query selecting word ids for content object (method was extracted to be reusable).
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     */
    private function setContentObjectWordsSelectQuery(SelectQuery $query)
    {
        $query->select('word_id')
            ->from($this->dbHandler->quoteTable('ezsearch_object_word_link'))
            ->where($query->expr->eq($this->dbHandler->quoteColumn('contentobject_id'), ':contentId'));
    }

    /**
     * Update object count for words (legacy db table: ezsearch_word).
     *
     * @param array $wordId list of word IDs
     * @param array $columns map of columns and values to be updated ([column => value])
     */
    private function updateWordObjectCount(array $wordId, array $columns)
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update($this->dbHandler->quoteTable('ezsearch_word'));

        foreach ($columns as $column => $value) {
            $query->set($this->dbHandler->quoteColumn($column), $value);
        }

        $query->where($query->expr->in($this->dbHandler->quoteColumn('id'), $wordId));

        $stmt = $query->prepare();
        $stmt->execute();
    }
}
