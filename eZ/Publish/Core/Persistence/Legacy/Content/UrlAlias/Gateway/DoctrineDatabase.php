<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\Query;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Language;
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

    private const URL_ALIAS_DATA_COLUMN_TYPE_MAP = [
        'id' => ParameterType::INTEGER,
        'link' => ParameterType::INTEGER,
        'is_alias' => ParameterType::INTEGER,
        'alias_redirects' => ParameterType::INTEGER,
        'is_original' => ParameterType::INTEGER,
        'action' => ParameterType::STRING,
        'action_type' => ParameterType::STRING,
        'lang_mask' => ParameterType::INTEGER,
        'text' => ParameterType::STRING,
        'parent' => ParameterType::INTEGER,
        'text_md5' => ParameterType::STRING,
    ];

    /**
     * Columns of database tables.
     *
     * @var array
     *
     * @todo remove after testing
     */
    protected $columns = [
        'ezurlalias_ml' => [
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
        ],
    ];

    /**
     * Doctrine database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     *
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Main URL database table name.
     *
     * @var string
     */
    protected $table;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $databasePlatform;

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
        $this->table = static::TABLE;
        $this->connection = $dbHandler->getConnection();
    }

    public function setTable($name)
    {
        $this->table = $name;
    }

    /**
     * Loads all list of aliases by given $locationId.
     */
    public function loadAllLocationEntries(int $locationId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(...$this->columns[$this->table])
            ->from($this->connection->quoteIdentifier($this->table))
            ->where('action = :action')
            ->andWhere('is_original = :is_original')
            ->setParameter('action', "eznode:{$locationId}", ParameterType::STRING)
            ->setParameter('is_original', 1, ParameterType::INTEGER);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
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
            $this->dbHandler->quoteTable($this->table)
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
            $this->dbHandler->quoteTable($this->table)
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
            $this->dbHandler->quoteTable($this->table)
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

    public function cleanupAfterPublish(
        string $action,
        int $languageId,
        int $newId,
        int $parentId,
        string $textMD5
    ): void {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select(
                'parent',
                'text_md5',
                'lang_mask'
            )
            ->from($this->connection->quoteIdentifier($this->table))
            // 1) Autogenerated aliases that match action and language...
            ->where(
                $expr->eq(
                    'action',
                    $query->createPositionalParameter($action, ParameterType::STRING)
                )
            )
            ->andWhere(
                $expr->eq(
                    'is_original',
                    $query->createPositionalParameter(1, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'is_alias',
                    $query->createPositionalParameter(0, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->gt(
                    $this->getDatabasePlatform()->getBitAndComparisonExpression(
                        'lang_mask',
                        $query->createPositionalParameter($languageId, ParameterType::INTEGER)
                    ),
                    0
                )
            )
            // 2) ...but not newly published entry
            ->andWhere(
                sprintf(
                    'NOT (%s)',
                    $expr->andX(
                        $expr->eq(
                            'parent',
                            $query->createPositionalParameter($parentId, ParameterType::INTEGER)
                        ),
                        $expr->eq(
                            'text_md5',
                            $query->createPositionalParameter($textMD5, ParameterType::STRING)
                        )
                    )
                )
            );

        $statement = $query->execute();

        $row = $statement->fetch(FetchMode::ASSOCIATIVE);

        if (!empty($row)) {
            $this->archiveUrlAliasForDeletedTranslation(
                (int)$row['lang_mask'],
                $languageId,
                (int)$row['parent'],
                $row['text_md5'],
                $newId
            );
        }
    }

    /**
     * Archive (remove or historize) obsolete URL aliases (for translations that were removed).
     *
     * @param int $languageMask all languages bit mask
     * @param int $languageId removed language Id
     * @param int $parent
     * @param string $textMD5 checksum
     * @param $linkId
     */
    private function archiveUrlAliasForDeletedTranslation($languageMask, $languageId, $parent, $textMD5, $linkId)
    {
        // If language mask is composite (consists of multiple languages) then remove given language from entry
        if ($languageMask & ~($languageId | 1)) {
            $this->removeTranslation($parent, $textMD5, $languageId);
        } else {
            // Otherwise mark entry as history
            $this->historize($parent, $textMD5, $linkId);
        }
    }

    public function historizeBeforeSwap($action, $languageMask)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable($this->table)
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
     * @param int $parentId
     * @param string $textMD5
     * @param int $newId
     */
    protected function historize($parentId, $textMD5, $newId)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable($this->table)
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
            $this->dbHandler->quoteTable($this->table)
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
     * This method is used by Handler::locationMoved(). Each row is separately historized
     * because future publishing needs to be able to take over history entries safely.
     *
     * @param mixed $id
     * @param mixed $link
     */
    public function historizeId($id, $link)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('parent'),
            $this->dbHandler->quoteColumn('text_md5')
        )->from(
            $this->dbHandler->quoteTable($this->table)
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_alias'),
                    $query->bindValue(0, null, \PDO::PARAM_INT)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('is_original'),
                    $query->bindValue(1, null, \PDO::PARAM_INT)
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

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $this->historize($row['parent'], $row['text_md5'], $link);
        }
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
            $this->dbHandler->quoteTable($this->table)
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
        $query = $this->connection->createQueryBuilder();
        $query->update($this->connection->quoteIdentifier($this->table));
        foreach ($values as $columnName => $value) {
            $query->set(
                $columnName,
                $query->createNamedParameter(
                    $value,
                    self::URL_ALIAS_DATA_COLUMN_TYPE_MAP[$columnName],
                    ":{$columnName}"
                )
            );
        }
        $query
            ->where(
                $query->expr()->eq(
                    'parent',
                    $query->createNamedParameter($parentId, ParameterType::INTEGER, ':parent')
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'text_md5',
                    $query->createNamedParameter($textMD5, ParameterType::STRING, ':text_md5')
                )
            );
        $query->execute();
    }

    public function insertRow(array $values): int
    {
        if (!isset($values['id'])) {
            $values['id'] = $this->getNextId();
        }
        if (!isset($values['link'])) {
            $values['link'] = $values['id'];
        }
        if (!isset($values['is_original'])) {
            $values['is_original'] = (((int)$values['id']) === ((int)$values['link']) ? 1 : 0);
        }
        if (!isset($values['is_alias'])) {
            $values['is_alias'] = 0;
        }
        if (!isset($values['alias_redirects'])) {
            $values['alias_redirects'] = 0;
        }
        if (
            !isset($values['action_type'])
            && preg_match('#^(.+):.*#', $values['action'], $matches)
        ) {
            $values['action_type'] = $matches[1];
        }
        if ($values['is_alias']) {
            $values['is_original'] = 1;
        }
        if ($values['action'] === 'nop:') {
            $values['is_original'] = 0;
        }

        $query = $this->connection->createQueryBuilder();
        $query->insert($this->connection->quoteIdentifier($this->table));
        foreach ($values as $columnName => $value) {
            $query->setValue(
                $columnName,
                $query->createNamedParameter(
                    $value,
                    self::URL_ALIAS_DATA_COLUMN_TYPE_MAP[$columnName],
                    ":{$columnName}"
                )
            );
        }
        $query->execute();

        return (int)$values['id'];
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

        return (int)$this->dbHandler->lastInsertId($sequence);
    }

    public function loadRow(int $parentId, string $textMD5): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(array_keys(self::URL_ALIAS_DATA_COLUMN_TYPE_MAP))
            ->from($this->connection->quoteIdentifier($this->table))
            ->where(
                $query->expr()->eq(
                    'parent',
                    $query->createPositionalParameter(
                        $parentId,
                        ParameterType::INTEGER
                    )
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'text_md5',
                    $query->createPositionalParameter(
                        $textMD5,
                        ParameterType::STRING
                    )
                )
            )
        ;

        $result = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        return false !== $result ? $result : [];
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
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();

        $count = count($urlHashes);
        foreach ($urlHashes as $level => $urlPartHash) {
            $tableAlias = $level !== $count - 1 ? $this->table . $level : 'u';
            $query
                ->addSelect(
                    array_map(
                        static function (string $columnName) use ($tableAlias) {
                            // do not alias data for top level url part
                            $columnAlias = 'u' === $tableAlias
                                ? $columnName
                                : "{$tableAlias}_{$columnName}";
                            $columnName = "{$tableAlias}.{$columnName}";

                            return "{$columnName} AS {$columnAlias}";
                        },
                        array_keys(self::URL_ALIAS_DATA_COLUMN_TYPE_MAP)
                    )
                )
                ->from($this->connection->quoteIdentifier($this->table), $tableAlias);

            $query
                ->andWhere(
                    $expr->eq(
                        "{$tableAlias}.text_md5",
                        $query->createPositionalParameter($urlPartHash, ParameterType::STRING)
                    )
                )
                ->andWhere(
                    $expr->eq(
                        "{$tableAlias}.parent",
                        // root entry has parent column set to 0
                        isset($previousTableName) ? $previousTableName . '.link' : $query->createPositionalParameter(
                            0,
                            ParameterType::INTEGER
                        )
                    )
                );

            $previousTableName = $tableAlias;
        }
        $query->setMaxResults(1);

        $result = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        return false !== $result ? $result : [];
    }

    public function loadAutogeneratedEntry($action, ?int $parentId = null): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from($this->connection->quoteIdentifier($this->table))
            ->where(
                $query->expr()->eq(
                    'action',
                    $query->createPositionalParameter($action, ParameterType::STRING)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'is_original',
                    $query->createPositionalParameter(1, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    'is_alias',
                    $query->createPositionalParameter(0, ParameterType::INTEGER)
                )
            );

        if (isset($parentId)) {
            $query->andWhere(
                $query->expr()->eq(
                    'parent',
                    $query->createPositionalParameter(
                        $parentId,
                        ParameterType::INTEGER
                    )
                )
            );
        }

        $entry = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        return false !== $entry ? $entry : [];
    }

    /**
     * Loads all data for the path identified by given $id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param int $id
     *
     * @return array
     */
    public function loadPathData($id)
    {
        $pathData = [];

        while ($id != 0) {
            /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
            $query = $this->dbHandler->createSelectQuery();
            $query->select(
                $this->dbHandler->quoteColumn('parent'),
                $this->dbHandler->quoteColumn('lang_mask'),
                $this->dbHandler->quoteColumn('text')
            )->from(
                $this->dbHandler->quoteTable($this->table)
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
                $pathDataArray = [];
                foreach ($pathData as $path) {
                    if (!isset($path[0]['text'])) {
                        continue;
                    }

                    $pathDataArray[] = $path[0]['text'];
                }

                $path = implode('/', $pathDataArray);
                throw new BadStateException(
                    'id',
                    "Unable to load path data, the path ...'{$path}' is broken, alias id '{$id}' not found. " .
                    'To fix all broken paths run the ezplatform:urls:regenerate-aliases command'
                );
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

        $hierarchyConditions = [];
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
            $this->dbHandler->quoteTable($this->table)
        )->where(
            $query->expr->lOr($hierarchyConditions)
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $rowsMap = [];
        foreach ($rows as $row) {
            $rowsMap[$row['action']][] = $row;
        }

        if (count($rowsMap) !== count($hierarchyData)) {
            throw new \RuntimeException('The path is corrupted.');
        }

        $data = [];
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
            $this->dbHandler->quoteTable($this->table)
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
            $this->dbHandler->quoteTable($this->table)
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
            $this->dbHandler->quoteTable($this->table)
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
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();
        $queryBuilder
            ->select('c.initial_language_id')
            ->from('ezcontentobject', 'c')
            ->join('c', 'ezcontentobject_tree', 't', $expr->eq('t.contentobject_id', 'c.id'))
            ->where(
                $expr->eq('t.node_id', ':locationId')
            )
            ->setParameter('locationId', $locationId, ParameterType::INTEGER)
        ;

        $statement = $queryBuilder->execute();
        $languageId = $statement->fetchColumn();

        if ($languageId === false) {
            throw new RuntimeException("Could not find Content for Location #{$locationId}");
        }

        return $languageId;
    }

    /**
     * Removes languageId of removed translation from lang_mask and deletes single language rows for multiple Locations.
     *
     * Note: URL aliases are not historized as translation removal from all Versions is permanent w/o preserving history.
     *
     * @param int $languageId Language Id to be removed
     * @param string[] $actions actions for which to perform the update
     */
    public function bulkRemoveTranslation($languageId, $actions)
    {
        $connection = $this->dbHandler->getConnection();
        /** @var \Doctrine\DBAL\Connection $connection */
        $query = $connection->createQueryBuilder();
        $query
            ->update($connection->quoteIdentifier($this->table))
            // parameter for bitwise operation has to be placed verbatim (w/o binding) for this to work cross-DBMS
            ->set('lang_mask', 'lang_mask & ~ ' . $languageId)
            ->where('action IN (:actions)')
            ->setParameter(':actions', $actions, Connection::PARAM_STR_ARRAY)
        ;
        $query->execute();

        // cleanup: delete single language rows (including alwaysAvailable)
        $query = $connection->createQueryBuilder();
        $query
            ->delete($this->table)
            ->where('action IN (:actions)')
            ->andWhere('lang_mask IN (0, 1)')
            ->setParameter(':actions', $actions, Connection::PARAM_STR_ARRAY)
        ;
        $query->execute();
    }

    /**
     * Archive (remove or historize) URL aliases for removed Translations.
     *
     * @param int $locationId
     * @param int $parentId
     * @param int[] $languageIds Language IDs of removed Translations
     */
    public function archiveUrlAliasesForDeletedTranslations($locationId, $parentId, array $languageIds)
    {
        // determine proper parent for linking historized entry
        $existingLocationEntry = $this->loadAutogeneratedEntry(
            'eznode:' . $locationId,
            $parentId
        );

        // filter existing URL alias entries by any of the specified removed languages
        $rows = $this->loadLocationEntriesMatchingMultipleLanguages(
            $locationId,
            $languageIds
        );

        // remove specific languages from a bit mask
        foreach ($rows as $row) {
            // filter mask to reduce the number of calls to storage engine
            $rowLanguageMask = (int) $row['lang_mask'];
            $languageIdsToBeRemoved = array_filter(
                $languageIds,
                function ($languageId) use ($rowLanguageMask) {
                    return $languageId & $rowLanguageMask;
                }
            );

            if (empty($languageIdsToBeRemoved)) {
                continue;
            }

            // use existing entry to link archived alias or use current alias id
            $linkToId = !empty($existingLocationEntry) ? $existingLocationEntry['id'] : $row['id'];
            foreach ($languageIdsToBeRemoved as $languageId) {
                $this->archiveUrlAliasForDeletedTranslation(
                    $row['lang_mask'],
                    $languageId,
                    $row['parent'],
                    $row['text_md5'],
                    $linkToId
                );
            }
        }
    }

    /**
     * Load list of aliases for given $locationId matching any of the Languages specified by $languageMask.
     *
     * @param int $locationId
     * @param int[] $languageIds
     *
     * @return array[]|\Generator
     */
    private function loadLocationEntriesMatchingMultipleLanguages($locationId, array $languageIds)
    {
        // note: alwaysAvailable for this use case is not relevant
        $languageMask = $this->languageMaskGenerator->generateLanguageMaskFromLanguageIds(
            $languageIds,
            false
        );

        $connection = $this->dbHandler->getConnection();
        /** @var \Doctrine\DBAL\Connection $connection */
        $query = $connection->createQueryBuilder();
        $query
            ->select('id', 'lang_mask', 'parent', 'text_md5')
            ->from($this->table)
            ->where('action = :action')
            // fetch rows matching any of the given Languages
            ->andWhere('lang_mask & :languageMask <> 0')
            ->setParameter(':action', 'eznode:' . $locationId)
            ->setParameter(':languageMask', $languageMask)
        ;

        $statement = $query->execute();
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $rows ?: [];
    }

    /**
     * Delete URL aliases pointing to non-existent Locations.
     *
     * @return int Number of affected rows.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteUrlAliasesWithoutLocation(): int
    {
        $dbPlatform = $this->connection->getDatabasePlatform();

        $subquery = $this->connection->createQueryBuilder();
        $subquery
            ->select('node_id')
            ->from('ezcontentobject_tree', 't')
            ->where(
                $subquery->expr()->eq(
                    't.node_id',
                    sprintf(
                        'CAST(%s as %s)',
                        $dbPlatform->getSubstringExpression($this->table . '.action', 8),
                        $this->getIntegerType($dbPlatform)
                    )
                )
            )
        ;

        $deleteQuery = $this->connection->createQueryBuilder();
        $deleteQuery
            ->delete($this->table)
            ->where(
                $deleteQuery->expr()->eq(
                    'action_type',
                    $deleteQuery->createPositionalParameter('eznode')
                )
            )
            ->andWhere(
                sprintf('NOT EXISTS (%s)', $subquery->getSQL())
            )
        ;

        return $deleteQuery->execute();
    }

    /**
     * Delete URL aliases pointing to non-existent parent nodes.
     *
     * @return int Number of affected rows.
     */
    public function deleteUrlAliasesWithoutParent(): int
    {
        $existingAliasesQuery = $this->getAllUrlAliasesQuery();

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete($this->table)
            ->where(
                $query->expr()->neq(
                    'parent',
                    $query->createPositionalParameter(0, \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $query->expr()->notIn(
                    'parent',
                    $existingAliasesQuery
                )
            )
        ;

        return $query->execute();
    }

    /**
     * Delete URL aliases which do not link to any existing URL alias node.
     *
     * Note: Typically link column value is used to determine original alias for an archived entries.
     */
    public function deleteUrlAliasesWithBrokenLink()
    {
        $existingAliasesQuery = $this->getAllUrlAliasesQuery();

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete($this->table)
            ->where(
                $query->expr()->neq('id', 'link')
            )
            ->andWhere(
                $query->expr()->notIn(
                    'link',
                    $existingAliasesQuery
                )
            )
        ;

        return $query->execute();
    }

    /**
     * Attempt repairing data corruption for broken archived URL aliases for Location,
     * assuming there exists restored original (current) entry.
     *
     * @param int $locationId
     */
    public function repairBrokenUrlAliasesForLocation(int $locationId)
    {
        $urlAliasesData = $this->getUrlAliasesForLocation($locationId);

        $originalUrlAliases = $this->filterOriginalAliases($urlAliasesData);

        if (count($originalUrlAliases) === count($urlAliasesData)) {
            // no archived aliases - nothing to fix
            return;
        }

        $updateQueryBuilder = $this->connection->createQueryBuilder();
        $expr = $updateQueryBuilder->expr();
        $updateQueryBuilder
            ->update('ezurlalias_ml')
            ->set('link', ':linkId')
            ->set('parent', ':newParentId')
            ->where(
                $expr->eq('action', ':action')
            )
            ->andWhere(
                $expr->eq(
                    'is_original',
                    $updateQueryBuilder->createNamedParameter(0, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq('parent', ':oldParentId')
            )
            ->andWhere(
                $expr->eq('text_md5', ':textMD5')
            )
            ->setParameter(':action', "eznode:{$locationId}");

        foreach ($urlAliasesData as $urlAliasData) {
            if ($urlAliasData['is_original'] === 1 || !isset($originalUrlAliases[$urlAliasData['lang_mask']])) {
                // ignore non-archived entries and deleted Translations
                continue;
            }

            $originalUrlAlias = $originalUrlAliases[$urlAliasData['lang_mask']];

            if ($urlAliasData['link'] === $originalUrlAlias['link']) {
                // ignore correct entries to avoid unnecessary updates
                continue;
            }

            $updateQueryBuilder
                ->setParameter(':linkId', $originalUrlAlias['link'], ParameterType::INTEGER)
                // attempt to fix missing parent case
                ->setParameter(
                    ':newParentId',
                    $urlAliasData['existing_parent'] ?? $originalUrlAlias['parent'],
                    ParameterType::INTEGER
                )
                ->setParameter(':oldParentId', $urlAliasData['parent'], ParameterType::INTEGER)
                ->setParameter(':textMD5', $urlAliasData['text_md5']);

            try {
                $updateQueryBuilder->execute();
            } catch (UniqueConstraintViolationException $e) {
                // edge case: if such row already exists, there's no way to restore history
                $this->deleteRow($urlAliasData['parent'], $urlAliasData['text_md5']);
            }
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteUrlNopAliasesWithoutChildren(): int
    {
        $platform = $this->connection->getDatabasePlatform();
        $queryBuilder = $this->connection->createQueryBuilder();

        // The wrapper select is needed for SQL "Derived Table Merge" issue for deleting
        $wrapperQueryBuilder = clone $queryBuilder;
        $selectQueryBuilder = clone $queryBuilder;
        $expressionBuilder = $queryBuilder->expr();

        $selectQueryBuilder
            ->select('u_parent.id AS inner_id')
            ->from($this->table, 'u_parent')
            ->leftJoin(
                'u_parent',
                $this->table,
                'u',
                $expressionBuilder->eq('u_parent.id', 'u.parent')
            )
            ->where(
                $expressionBuilder->eq(
                    'u_parent.action_type',
                    ':actionType'
                )
            )
            ->groupBy('u_parent.id')
            ->having(
                $expressionBuilder->eq($platform->getCountExpression('u.id'), 0)
            );

        $wrapperQueryBuilder
            ->select('inner_id')
            ->from(
                sprintf('(%s)', $selectQueryBuilder), 'wrapper'
            )
            ->where('id = inner_id');

        $queryBuilder
            ->delete($this->table)
            ->where(
                sprintf('EXISTS (%s)', $wrapperQueryBuilder)
            )
            ->setParameter('actionType', 'nop');

        return $queryBuilder->execute();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllChildrenAliases(int $parentId): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expressionBuilder = $queryBuilder->expr();

        $queryBuilder
            ->select('parent', 'text_md5')
            ->from($this->table)
            ->where(
                $expressionBuilder->eq(
                    'parent',
                    $queryBuilder->createPositionalParameter($parentId, ParameterType::INTEGER)
                )
            )->andWhere(
                $expressionBuilder->eq(
                    'is_alias',
                    $queryBuilder->createPositionalParameter(1, ParameterType::INTEGER)
                )
            );

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Filter from the given result set original (current) only URL aliases and index them by language_mask.
     *
     * Note: each language_mask can have one URL Alias.
     *
     * @param array $urlAliasesData
     *
     * @return array
     */
    private function filterOriginalAliases(array $urlAliasesData): array
    {
        $originalUrlAliases = array_filter(
            $urlAliasesData,
            function ($urlAliasData) {
                // filter is_original=true ignoring broken parent records (cleaned up elsewhere)
                return (bool)$urlAliasData['is_original'] && $urlAliasData['existing_parent'] !== null;
            }
        );
        // return language_mask-indexed array
        return array_combine(
            array_column($originalUrlAliases, 'lang_mask'),
            $originalUrlAliases
        );
    }

    /**
     * Get subquery for IDs of all URL aliases.
     *
     * @return string Query
     */
    private function getAllUrlAliasesQuery(): string
    {
        $existingAliasesQueryBuilder = $this->connection->createQueryBuilder();
        $innerQueryBuilder = $this->connection->createQueryBuilder();

        return $existingAliasesQueryBuilder
            ->select('tmp.id')
            ->from(
                // nest subquery to avoid same-table update error
                '(' . $innerQueryBuilder->select('id')->from($this->table)->getSQL() . ')',
                'tmp'
            )
            ->getSQL();
    }

    /**
     * Get DBMS-specific integer type.
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $databasePlatform
     *
     * @return string
     */
    private function getIntegerType(AbstractPlatform $databasePlatform): string
    {
        switch ($databasePlatform->getName()) {
            case 'mysql':
                return 'signed';
            default:
                return 'integer';
        }
    }

    /**
     * Get all URL aliases for the given Location (including archived ones).
     *
     * @param int $locationId
     *
     * @return array
     */
    protected function getUrlAliasesForLocation(int $locationId): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select(
                't1.id',
                't1.is_original',
                't1.lang_mask',
                't1.link',
                't1.parent',
                // show existing parent only if its row exists, special case for root parent
                'CASE t1.parent WHEN 0 THEN 0 ELSE t2.id END AS existing_parent',
                't1.text_md5'
            )
            ->from($this->table, 't1')
            // selecting t2.id above will result in null if parent is broken
            ->leftJoin('t1', $this->table, 't2', $queryBuilder->expr()->eq('t1.parent', 't2.id'))
            ->where(
                $queryBuilder->expr()->eq(
                    't1.action',
                    $queryBuilder->createPositionalParameter("eznode:{$locationId}")
                )
            );

        return $queryBuilder->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Delete URL alias row by its primary composite key.
     *
     * @param int $parentId
     * @param string $textMD5
     *
     * @return int number of affected rows
     */
    private function deleteRow(int $parentId, string $textMD5): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();
        $queryBuilder
            ->delete($this->table)
            ->where(
                $expr->andX(
                    $expr->eq(
                        'parent',
                        $queryBuilder->createPositionalParameter($parentId, ParameterType::INTEGER)
                    ),
                    $expr->eq(
                        'text_md5',
                        $queryBuilder->createPositionalParameter($textMD5)
                    )
                )
            );

        return $queryBuilder->execute();
    }

    public function loadRowsForPath(int $parentId, string $textHash): array
    {
        $query = $this->connection->createQueryBuilder();
        $expressionBuilder = $query->expr();
        $query
            ->select(array_keys(self::URL_ALIAS_DATA_COLUMN_TYPE_MAP))
            ->from($this->connection->quoteIdentifier($this->table))
            ->where(
                $expressionBuilder->eq(
                    'parent',
                    $query->createPositionalParameter(
                        $parentId,
                        ParameterType::INTEGER
                    )
                )
            )
            ->andWhere(
                $expressionBuilder->eq(
                    'text_md5',
                    $query->createPositionalParameter(
                        $textHash,
                        ParameterType::STRING
                    )
                )
            );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    private function appendLanguageMaskMatchConstraint(
        QueryBuilder $query,
        int $languageMask,
        string $langMaskColumnName = 'lang_mask',
        bool $matchAlwaysAvailable = false
    ): QueryBuilder {
        $query->andWhere(
            // lang_mask & $languageMask > $matchAlwaysAvailable
            $this->getLanguageMaskMatchConstraint(
                $query,
                $languageMask,
                $langMaskColumnName,
                $matchAlwaysAvailable
            )
        );

        return $query;
    }

    private function getLanguageMaskMatchConstraint(
        QueryBuilder $query,
        int $languageMask,
        string $langMaskColumnName = 'lang_mask',
        bool $matchAlwaysAvailable = false
    ): string {
        try {
            $databasePlatform = $query->getConnection()->getDatabasePlatform();
        } catch (DBALException $e) {
            throw DatabaseException::wrap($e);
        }
        // intentionally 0 to match, see bitwise operation
        $greaterThanValue = $matchAlwaysAvailable ? 0 : 1;

        $expressionBuilder = $query->expr();
        // lang_mask & $languageMask > $matchAlwaysAvailable
        return $expressionBuilder->gt(
            $databasePlatform->getBitAndComparisonExpression(
                $langMaskColumnName,
                $query->createPositionalParameter($languageMask, ParameterType::INTEGER)
            ),
            // "> 0" or "> 1" to match always available or not to, respectively
            $query->createPositionalParameter($greaterThanValue, ParameterType::INTEGER)
        );
    }

    private function buildSelectPathElementQuery(int $parentId, string $textHash): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $expressionBuilder = $query->expr();
        $query
            ->select(array_keys(self::URL_ALIAS_DATA_COLUMN_TYPE_MAP))
            ->from($this->connection->quoteIdentifier($this->table))
            ->where(
                $expressionBuilder->eq(
                    'parent',
                    $query->createPositionalParameter(
                        $parentId,
                        ParameterType::INTEGER
                    )
                )
            )
            ->andWhere(
                $expressionBuilder->eq(
                    'text_md5',
                    $query->createPositionalParameter(
                        $textHash,
                        ParameterType::STRING
                    )
                )
            );

        return $query;
    }

    /**
     * Lazy database platform (engine) fetch (creates a connection, if not opened yet).
     *
     * @return \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private function getDatabasePlatform(): AbstractPlatform
    {
        if (null === $this->databasePlatform) {
            try {
                $this->databasePlatform = $this->connection->getDatabasePlatform();
            } catch (DBALException $e) {
                throw DatabaseException::wrap($e);
            }
        }

        return $this->databasePlatform;
    }
}
