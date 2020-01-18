<?php

/**
 * File containing the DoctrineDatabase Language Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use RuntimeException;

/**
 * Doctrine database based Language Gateway.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * The native Doctrine connection.
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * Creates a new Doctrine database Section Gateway.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
        $this->connection = $dbHandler->getConnection();
        $this->dbPlatform = $this->connection->getDatabasePlatform();
    }

    /**
     * Inserts the given $language.
     *
     * @param Language $language
     *
     * @return int ID of the new language
     */
    public function insertLanguage(Language $language)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                $this->dbPlatform->getMaxExpression('id')
            )
            ->from(self::CONTENT_LANGUAGE_TABLE);

        $statement = $query->execute();

        $lastId = (int)$statement->fetchColumn();

        // Legacy only supports 8 * PHP_INT_SIZE - 2 languages:
        // One bit cannot be used because PHP uses signed integers and a second one is reserved for the
        // "always available flag".
        if ($lastId == (2 ** (8 * PHP_INT_SIZE - 2))) {
            throw new RuntimeException('Maximum number of languages reached.');
        }
        // Next power of 2 for bit masks
        $nextId = ($lastId !== 0 ? $lastId << 1 : 2);

        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::CONTENT_LANGUAGE_TABLE)
            ->values(
                [
                    'id' => ':id',
                    'locale' => ':language_code',
                    'name' => ':name',
                    'disabled' => ':disabled',
                ]
            )
            ->setParameter('id', $nextId, ParameterType::INTEGER);

        $this->setLanguageQueryParameters($query, $language);

        $query->execute();

        return $nextId;
    }

    /**
     * Sets columns in $query from $language.
     */
    protected function setLanguageQueryParameters(QueryBuilder $query, Language $language)
    {
        $query
            ->setParameter('language_code', $language->languageCode, ParameterType::STRING)
            ->setParameter('name', $language->name, ParameterType::STRING)
            ->setParameter('disabled', (int)!$language->isEnabled, ParameterType::INTEGER);
    }

    /**
     * Updates the data of the given $language.
     *
     * @param Language $language
     */
    public function updateLanguage(Language $language)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(self::CONTENT_LANGUAGE_TABLE)
            ->set('locale', ':language_code')
            ->set('name', ':name')
            ->set('disabled', ':disabled');

        $this->setLanguageQueryParameters($query, $language);

        $query->where(
            $query->expr()->eq(
                'id',
                $query->createNamedParameter($language->id, ParameterType::INTEGER, ':id')
            )
        );

        $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguageListData(array $ids): iterable
    {
        $query = $this->createFindQuery();
        $query
            ->where('id IN (:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguageListDataByLanguageCode(array $languageCodes): iterable
    {
        $query = $this->createFindQuery();
        $query
            ->where('locale IN (:locale)')
            ->setParameter('locale', $languageCodes, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll();
    }

    /**
     * Creates a Language find query.
     */
    protected function createFindQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id', 'locale', 'name', 'disabled')
            ->from(self::CONTENT_LANGUAGE_TABLE);

        return $query;
    }

    /**
     * Loads the data for all languages.
     *
     * @return string[][]
     */
    public function loadAllLanguagesData()
    {
        $query = $this->createFindQuery();

        return $query->execute()->fetchAll();
    }

    /**
     * Deletes the language with $id.
     *
     * @param int $id
     */
    public function deleteLanguage($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::CONTENT_LANGUAGE_TABLE)
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }

    /**
     * Check whether a language may be deleted.
     *
     * @param int $id
     *
     * @return bool
     */
    public function canDeleteLanguage($id)
    {
        // note: at some point this should be delegated to specific gateways
        foreach (self::MULTILINGUAL_TABLES_COLUMNS as $tableName => $columns) {
            $languageMaskColumn = $columns[0];
            $languageIdColumn = $columns[1] ?? null;
            if (
                $this->countTableData($id, $tableName, $languageMaskColumn, $languageIdColumn) > 0
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Count table data rows related to the given language.
     *
     * @param string|null $languageIdColumn optional column name containing explicit language id
     */
    private function countTableData(
        int $languageId,
        string $tableName,
        string $languageMaskColumn,
        ?string $languageIdColumn = null
    ): int {
        $query = $this->connection->createQueryBuilder();
        $query
            // avoiding using "*" as count argument, but don't specify column name because it varies
            ->select($this->dbPlatform->getCountExpression(1))
            ->from($tableName)
            ->where(
                $query->expr()->gt(
                    $this->dbPlatform->getBitAndComparisonExpression(
                        $languageMaskColumn,
                        $query->createPositionalParameter($languageId, ParameterType::INTEGER)
                    ),
                    0
                )
            );
        if (null !== $languageIdColumn) {
            $query
                ->orWhere(
                    $query->expr()->eq(
                        $languageIdColumn,
                        $query->createPositionalParameter($languageId, ParameterType::INTEGER)
                    )
                );
        }

        return (int)$query->execute()->fetchColumn();
    }
}
