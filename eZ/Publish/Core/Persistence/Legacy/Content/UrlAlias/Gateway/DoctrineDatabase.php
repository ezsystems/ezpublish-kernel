<?php

/**
 * File containing the DoctrineDatabase UrlAlias Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence\Database\Query;
use RuntimeException;

/**
 * UrlAlias Gateway.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems.
     */
    const MAX_LIMIT = 1073741824;

    /**
     * Columns of database tables.
     *
     * @var array
     *
     * @todo remove after testing
     */
    protected $columns = array(
        'ezurlalias_ml' => array(
            'action',
            'action_type',
            'alias_redirects',
            'id',
            'is_alias',
            'is_original',
            'lang_mask',
            'link',
            'parent',
            'text',
            'text_md5',
        ),
    );

    /**
     * Doctrine database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new DoctrineDatabase UrlAlias Gateway.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        LanguageMaskGenerator $languageMaskGenerator
    ) {
        $this->dbHandler = $dbHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Loads list of aliases by given $locationId.
     *
     * @param mixed $locationId
     * @param bool $custom
     * @param mixed $languageId
     *
     * @return array
     */
    public function loadLocationEntries($locationId, $custom = false, $languageId = false)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('link'),
            $this->dbHandler->quoteColumn('is_alias'),
            $this->dbHandler->quoteColumn('alias_redirects'),
            $this->dbHandler->quoteColumn('lang_mask'),
            $this->dbHandler->quoteColumn('is_original'),
            $this->dbHandler->quoteColumn('parent'),
            $this->dbHandler->quoteColumn('text'),
            $this->dbHandler->quoteColumn('text_md5'),
            $this->dbHandler->quoteColumn('action')
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action'),
                    $query->bindValue("eznode:{$locationId}", null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(
                        $custom ? 1 : 0,
                        null,
                        \PDO::PARAM_INT
                    )
                )
            )
        );

        if ($languageId !== false) {
            $query->where(
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('lang_mask'),
                        $query->bindValue($languageId, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads paged list of global aliases.
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function listGlobalEntries($languageCode = null, $offset = 0, $limit = -1)
    {
        $limit = $limit === -1 ? self::MAX_LIMIT : $limit;

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('action'),
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('link'),
            $this->dbHandler->quoteColumn('is_alias'),
            $this->dbHandler->quoteColumn('alias_redirects'),
            $this->dbHandler->quoteColumn('lang_mask'),
            $this->dbHandler->quoteColumn('is_original'),
            $this->dbHandler->quoteColumn('parent'),
            $this->dbHandler->quoteColumn('text_md5')
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action_type'),
                    $query->bindValue('module', null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                )
            )
        )->limit(
            $limit,
            $offset
        );
        if (isset($languageCode)) {
            $query->where(
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('lang_mask'),
                        $query->bindValue(
                            $this->languageMaskGenerator->generateLanguageIndicator($languageCode, false),
                            null,
                            \PDO::PARAM_INT
                        )
                    ),
                    0
                )
            );
        }
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns boolean indicating if the row with given $id is special root entry.
     *
     * Special root entry entry will have parentId=0 and text=''.
     * In standard installation this entry will point to location with id=2.
     *
     * @param mixed $id
     *
     * @return bool
     */
    public function isRootEntry($id)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('text'),
            $this->dbHandler->quoteColumn('parent')
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );
        $statement = $query->prepare();
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return strlen($row['text']) == 0 && $row['parent'] == 0;
    }

    /**
     * Downgrades autogenerated entry matched by given $action and $languageId and negatively matched by
     * composite primary key.
     *
     * If language mask of the found entry is composite (meaning it consists of multiple language ids) given
     * $languageId will be removed from mask. Otherwise entry will be marked as history.
     *
     * @param string $action
     * @param mixed $languageId
     * @param mixed $newId
     * @param mixed $parentId
     * @param string $textMD5
     */
    public function cleanupAfterPublish($action, $languageId, $newId, $parentId, $textMD5)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('parent'),
            $this->dbHandler->quoteColumn('text_md5'),
            $this->dbHandler->quoteColumn('lang_mask')
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                // 1) Autogenerated aliases that match action and language...
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action'),
                    $query->bindValue($action, null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(0, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('lang_mask'),
                        $query->bindValue($languageId, null, \PDO::PARAM_INT)
                    ),
                    0
                ),
                // 2) ...but not newly published entry
                $query->expr->not(
                    $query->expr->lAnd(
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn('parent'),
                            $query->bindValue($parentId, null, \PDO::PARAM_INT)
                        ),
                        $query->expr->eq(
                            $this->dbHandler->quoteColumn('text_md5'),
                            $query->bindValue($textMD5, null, \PDO::PARAM_STR)
                        )
                    )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!empty($row)) {
            // If language mask is composite (consists of multiple languages) then remove given language from entry
            if ($row['lang_mask'] & ~($languageId | 1)) {
                $this->removeTranslation($row['parent'], $row['text_md5'], $languageId);
            } else {
                // Otherwise mark entry as history
                $this->historize($row['parent'], $row['text_md5'], $newId);
            }
        }
    }

    public function historizeBeforeSwap($action, $languageMask)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->set(
            $this->dbHandler->quoteColumn('is_original'),
            $query->bindValue(0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $query->bindValue(
                $this->getNextId(),
                null,
                \PDO::PARAM_INT
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action'),
                    $query->bindValue($action, null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('lang_mask'),
                        $query->bindValue($languageMask & ~1, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Updates single row matched by composite primary key.
     *
     * Sets "is_original" to 0 thus marking entry as history.
     *
     * Re-links history entries.
     *
     * When location alias is published we need to check for new history entries created with self::downgrade()
     * with the same action and language, update their "link" column with id of the published entry.
     * History entry "id" column is moved to next id value so that all active (non-history) entries are kept
     * under the same id.
     *
     * @param mixed $parentId
     * @param string $textMD5
     */
    protected function historize($parentId, $textMD5, $newId)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->set(
            $this->dbHandler->quoteColumn('is_original'),
            $query->bindValue(0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('link'),
            $query->bindValue($newId, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $query->bindValue(
                $this->getNextId(),
                null,
                \PDO::PARAM_INT
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('text_md5'),
                    $query->bindValue($textMD5, null, \PDO::PARAM_STR)
                )
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Updates single row data matched by composite primary key.
     *
     * Removes given $languageId from entry's language mask
     *
     * @param mixed $parentId
     * @param string $textMD5
     * @param mixed $languageId
     */
    protected function removeTranslation($parentId, $textMD5, $languageId)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->set(
            $this->dbHandler->quoteColumn('lang_mask'),
            $query->expr->bitAnd(
                $this->dbHandler->quoteColumn('lang_mask'),
                $query->bindValue(~$languageId, null, \PDO::PARAM_INT)
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('text_md5'),
                    $query->bindValue($textMD5, null, \PDO::PARAM_STR)
                )
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Marks all entries with given $id as history entries.
     *
     * This method is used by Handler::locationMoved(). For this reason rows are not updated with next id value as
     * all entries with given id are being marked as history and there is no need for id separation.
     * Thus only "link" and "is_original" columns are updated.
     *
     * @param mixed $id
     * @param mixed $link
     */
    public function historizeId($id, $link)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->set(
            $this->dbHandler->quoteColumn('is_original'),
            $query->bindValue(0, null, \PDO::PARAM_INT)
        )->set(
            $this->dbHandler->quoteColumn('link'),
            $query->bindValue($link, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(0, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action_type'),
                    $query->bindValue('eznode', null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('link'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                )
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Updates parent id of autogenerated entries.
     *
     * Update includes history entries.
     *
     * @param mixed $oldParentId
     * @param mixed $newParentId
     */
    public function reparent($oldParentId, $newParentId)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->set(
            $this->dbHandler->quoteColumn('parent'),
            $query->bindValue($newParentId, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(0, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($oldParentId, null, \PDO::PARAM_INT)
                )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Updates single row data matched by composite primary key.
     *
     * Use optional parameter $languageMaskMatch to additionally limit the query match with languages.
     *
     * @param mixed $parentId
     * @param string $textMD5
     * @param array $values associative array with column names as keys and column values as values
     */
    public function updateRow($parentId, $textMD5, array $values)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update($this->dbHandler->quoteTable('ezurlalias_ml'));
        $this->setQueryValues($query, $values);
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('text_md5'),
                    $query->bindValue($textMD5, null, \PDO::PARAM_STR)
                )
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Inserts new row in urlalias_ml table.
     *
     * @param array $values
     *
     * @return mixed
     */
    public function insertRow(array $values)
    {
        // @todo remove after testing
        if (
            !isset($values['text']) ||
            !isset($values['text_md5']) ||
            !isset($values['action']) ||
            !isset($values['parent']) ||
            !isset($values['lang_mask'])) {
            throw new \Exception('value set is incomplete: ' . var_export($values, true) . ", can't execute insert");
        }
        if (!isset($values['id'])) {
            $values['id'] = $this->getNextId();
        }
        if (!isset($values['link'])) {
            $values['link'] = $values['id'];
        }
        if (!isset($values['is_original'])) {
            $values['is_original'] = ($values['id'] == $values['link'] ? 1 : 0);
        }
        if (!isset($values['is_alias'])) {
            $values['is_alias'] = 0;
        }
        if (!isset($values['alias_redirects'])) {
            $values['alias_redirects'] = 0;
        }
        if (!isset($values['action_type'])) {
            if (preg_match('#^(.+):.*#', $values['action'], $matches)) {
                $values['action_type'] = $matches[1];
            }
        }
        if ($values['is_alias']) {
            $values['is_original'] = 1;
        }
        if ($values['action'] === 'nop:') {
            $values['is_original'] = 0;
        }

        /** @var $query \eZ\Publish\Core\Persistence\Database\InsertQuery */
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto($this->dbHandler->quoteTable('ezurlalias_ml'));
        $this->setQueryValues($query, $values);
        $query->prepare()->execute();

        return $values['id'];
    }

    /**
     * Sets value for insert or update query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\Query|\eZ\Publish\Core\Persistence\Database\InsertQuery|\eZ\Publish\Core\Persistence\Database\UpdateQuery $query
     * @param array $values
     *
     * @throws \Exception
     */
    protected function setQueryValues(Query $query, $values)
    {
        foreach ($values as $column => $value) {
            // @todo remove after testing
            if (!in_array($column, $this->columns['ezurlalias_ml'])) {
                throw new \Exception("unknown column '$column' for table 'ezurlalias_ml'");
            }
            switch ($column) {
                case 'text':
                case 'action':
                case 'text_md5':
                case 'action_type':
                    $pdoDataType = \PDO::PARAM_STR;
                    break;
                default:
                    $pdoDataType = \PDO::PARAM_INT;
            }
            $query->set(
                $this->dbHandler->quoteColumn($column),
                $query->bindValue($value, null, $pdoDataType)
            );
        }
    }

    /**
     * Returns next value for "id" column.
     *
     * @return mixed
     */
    public function getNextId()
    {
        $sequence = $this->dbHandler->getSequenceName('ezurlalias_ml_incr', 'id');
        /** @var $query \eZ\Publish\Core\Persistence\Database\InsertQuery */
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable('ezurlalias_ml_incr')
        );
        // ezcDatabase does not abstract the "auto increment id"
        // INSERT INTO ezurlalias_ml_incr VALUES(DEFAULT) is not an option due
        // to this mysql bug: http://bugs.mysql.com/bug.php?id=42270
        // as a result we are forced to check which database is currently used
        // to generate the correct SQL query
        // see https://jira.ez.no/browse/EZP-20652
        if ($this->dbHandler->useSequences()) {
            $query->set(
                $this->dbHandler->quoteColumn('id'),
                "nextval('{$sequence}')"
            );
        } else {
            $query->set(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue(null, null, \PDO::PARAM_NULL)
            );
        }
        $query->prepare()->execute();

        return $this->dbHandler->lastInsertId($sequence);
    }

    /**
     * Loads single row data matched by composite primary key.
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return array
     */
    public function loadRow($parentId, $textMD5)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select('*')->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('text_md5'),
                    $query->bindValue($textMD5, null, \PDO::PARAM_STR)
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads complete URL alias data by given array of path hashes.
     *
     * @param string[] $urlHashes URL string hashes
     *
     * @return array
     */
    public function loadUrlAliasData(array $urlHashes)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();

        $count = count($urlHashes);
        foreach ($urlHashes as $level => $urlPartHash) {
            $tableName = 'ezurlalias_ml' . ($level === $count - 1 ? '' : $level);

            if ($level === $count - 1) {
                $query->select(
                    $this->dbHandler->quoteColumn('id', $tableName),
                    $this->dbHandler->quoteColumn('link', $tableName),
                    $this->dbHandler->quoteColumn('is_alias', $tableName),
                    $this->dbHandler->quoteColumn('alias_redirects', $tableName),
                    $this->dbHandler->quoteColumn('is_original', $tableName),
                    $this->dbHandler->quoteColumn('action', $tableName),
                    $this->dbHandler->quoteColumn('action_type', $tableName),
                    $this->dbHandler->quoteColumn('lang_mask', $tableName),
                    $this->dbHandler->quoteColumn('text', $tableName),
                    $this->dbHandler->quoteColumn('parent', $tableName),
                    $this->dbHandler->quoteColumn('text_md5', $tableName)
                )->from(
                    $this->dbHandler->quoteTable('ezurlalias_ml')
                );
            } else {
                $query->select(
                    $this->dbHandler->aliasedColumn($query, 'id', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'link', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'is_alias', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'alias_redirects', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'is_original', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'action', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'action_type', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'lang_mask', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'text', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'parent', $tableName),
                    $this->dbHandler->aliasedColumn($query, 'text_md5', $tableName)
                )->from(
                    $query->alias('ezurlalias_ml', $tableName)
                );
            }

            $query->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('text_md5', $tableName),
                        $query->bindValue($urlPartHash, null, \PDO::PARAM_STR)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('parent', $tableName),
                        // root entry has parent column set to 0
                        isset($previousTableName) ? $this->dbHandler->quoteColumn('link', $previousTableName) : $query->bindValue(0, null, \PDO::PARAM_INT)
                    )
                )
            );

            $previousTableName = $tableName;
        }
        $query->limit(1);

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads autogenerated entry id by given $action and optionally $parentId.
     *
     * @param string $action
     * @param mixed|null $parentId
     *
     * @return array
     */
    public function loadAutogeneratedEntry($action, $parentId = null)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            '*'
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action'),
                    $query->bindValue($action, null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(0, null, \PDO::PARAM_INT)
                )
            )
        );

        if (isset($parentId)) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads all data for the path identified by given $id.
     *
     * @throws \RuntimeException
     *
     * @param mixed $id
     *
     * @return array
     */
    public function loadPathData($id)
    {
        $pathData = array();

        while ($id != 0) {
            /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
            $query = $this->dbHandler->createSelectQuery();
            $query->select(
                $this->dbHandler->quoteColumn('parent'),
                $this->dbHandler->quoteColumn('lang_mask'),
                $this->dbHandler->quoteColumn('text')
            )->from(
                $this->dbHandler->quoteTable('ezurlalias_ml')
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                )
            );

            $statement = $query->prepare();
            $statement->execute();

            $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($rows)) {
                // Normally this should never happen
                // @todo remove throw when tested
                $path = implode('/', $pathData);
                throw new \RuntimeException("Path ({$path}...) is broken, last id is '{$id}': " . __METHOD__);
            }

            $id = $rows[0]['parent'];
            array_unshift($pathData, $rows);
        }

        return $pathData;
    }

    /**
     * Loads path data identified by given ordered array of hierarchy data.
     *
     * The first entry in $hierarchyData corresponds to the top-most path element in the path, the second entry the
     * child of the first path element and so on.
     * This method is faster than self::getPath() since it can fetch all elements using only one query, but can be used
     * only for autogenerated paths.
     *
     * @param array $hierarchyData
     *
     * @return array
     */
    public function loadPathDataByHierarchy(array $hierarchyData)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();

        $hierarchyConditions = array();
        foreach ($hierarchyData as $levelData) {
            $hierarchyConditions[] = $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue(
                        $levelData['parent'],
                        null,
                        \PDO::PARAM_INT
                    )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action'),
                    $query->bindValue(
                        $levelData['action'],
                        null,
                        \PDO::PARAM_STR
                    )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id'),
                    $query->bindValue(
                        $levelData['id'],
                        null,
                        \PDO::PARAM_INT
                    )
                )
            );
        }

        $query->select(
            $this->dbHandler->quoteColumn('action'),
            $this->dbHandler->quoteColumn('lang_mask'),
            $this->dbHandler->quoteColumn('text')
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lOr($hierarchyConditions)
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $rowsMap = array();
        foreach ($rows as $row) {
            $rowsMap[$row['action']][] = $row;
        }

        if (count($rowsMap) !== count($hierarchyData)) {
            throw new \RuntimeException('The path is corrupted.');
        }

        $data = array();
        foreach ($hierarchyData as $levelData) {
            $data[] = $rowsMap[$levelData['action']];
        }

        return $data;
    }

    /**
     * Deletes single custom alias row matched by composite primary key.
     *
     * @param mixed $parentId
     * @param string $textMD5
     *
     * @return bool
     */
    public function removeCustomAlias($parentId, $textMD5)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\DeleteQuery */
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('text_md5'),
                    $query->bindValue($textMD5, null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->rowCount() === 1 ?: false;
    }

    /**
     * Deletes all rows with given $action and optionally $id.
     *
     * If $id is set only autogenerated entries will be removed.
     *
     * @param mixed $action
     * @param mixed|null $id
     *
     * @return bool
     */
    public function remove($action, $id = null)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\DeleteQuery */
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('action'),
                $query->bindValue($action, null, \PDO::PARAM_STR)
            )
        );

        if ($id !== null) {
            $query->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('is_alias'),
                        $query->bindValue(0, null, \PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('id'),
                        $query->bindValue($id, null, \PDO::PARAM_INT)
                    )
                )
            );
        }

        $query->prepare()->execute();
    }

    /**
     * Loads all autogenerated entries with given $parentId with optionally included history entries.
     *
     * @param mixed $parentId
     * @param bool $includeHistory
     *
     * @return array
     */
    public function loadAutogeneratedEntries($parentId, $includeHistory = false)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            '*'
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('parent'),
                    $query->bindValue($parentId, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('action_type'),
                    $query->bindValue('eznode', null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(0, null, \PDO::PARAM_INT)
                )
            )
        );

        if (!$includeHistory) {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getLocationContentMainLanguageId($locationId)
    {
        $dbHandler = $this->dbHandler;
        $query = $dbHandler->createSelectQuery();
        $query
            ->select($dbHandler->quoteColumn('initial_language_id', 'ezcontentobject'))
            ->from($dbHandler->quoteTable('ezcontentobject'))
            ->innerJoin(
                $dbHandler->quoteTable('ezcontentobject_tree'),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                        $dbHandler->quoteColumn('id', 'ezcontentobject')
                    ),
                    $query->expr->eq(
                        $dbHandler->quoteColumn('node_id', 'ezcontentobject_tree'),
                        $dbHandler->quoteColumn('main_node_id', 'ezcontentobject_tree')
                    ),
                    $query->expr->eq(
                        $dbHandler->quoteColumn('node_id', 'ezcontentobject_tree'),
                        $query->bindValue($locationId, null, \PDO::PARAM_INT)
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
        $languageId = $statement->fetchColumn();

        if ($languageId === false) {
            throw new RuntimeException("Could not find Content for Location #{$locationId}");
        }

        return $languageId;
    }
}
