<?php

/**
 * File containing the DoctrineDatabase Language Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\Query;
use RuntimeException;

/**
 * Doctrine database based Language Gateway.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new Doctrine database Section Gateway.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->max($this->dbHandler->quoteColumn('id'))
        )->from($this->dbHandler->quoteTable('ezcontent_language'));

        $statement = $query->prepare();
        $statement->execute();

        $lastId = (int)$statement->fetchColumn();

        // Legacy only supports 8 * PHP_INT_SIZE - 2 languages:
        // One bit cannot be used because PHP uses signed integers and a second one is reserved for the
        // "always available flag".
        if ($lastId == (2 ** (8 * PHP_INT_SIZE - 2))) {
            throw new RuntimeException('Maximum number of languages reached!');
        }
        // Next power of 2 for bit masks
        $nextId = ($lastId !== 0 ? $lastId << 1 : 2);

        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable('ezcontent_language')
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $query->bindValue($nextId, null, \PDO::PARAM_INT)
        );
        $this->setCommonLanguageColumns($query, $language);

        $query->prepare()->execute();

        return $nextId;
    }

    /**
     * Sets columns in $query from $language.
     *
     * @param \eZ\Publish\Core\Persistence\Database\Query $query
     * @param \eZ\Publish\SPI\Persistence\Content\Language $language
     */
    protected function setCommonLanguageColumns(Query $query, Language $language)
    {
        $query->set(
            $this->dbHandler->quoteColumn('locale'),
            $query->bindValue($language->languageCode)
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $query->bindValue($language->name)
        )->set(
            $this->dbHandler->quoteColumn('disabled'),
            $query->bindValue(
                ((int)(!$language->isEnabled)),
                null,
                \PDO::PARAM_INT
            )
        );
    }

    /**
     * Updates the data of the given $language.
     *
     * @param Language $language
     */
    public function updateLanguage(Language $language)
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update($this->dbHandler->quoteTable('ezcontent_language'));

        $this->setCommonLanguageColumns($query, $language);

        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($language->id, null, \PDO::PARAM_INT)
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Loads data for the Language with $id.
     *
     * @param int $id
     *
     * @return string[][]
     */
    public function loadLanguageData($id)
    {
        $query = $this->createFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads data for the Language with Language Code (eg: eng-GB).
     *
     * @param string $languageCode
     *
     * @return string[][]
     */
    public function loadLanguageDataByLanguageCode($languageCode)
    {
        $query = $this->createFindQuery();
        $query->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('locale'),
                $query->bindValue($languageCode, null, \PDO::PARAM_STR)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Creates a Language find query.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    protected function createFindQuery()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('locale'),
            $this->dbHandler->quoteColumn('name'),
            $this->dbHandler->quoteColumn('disabled')
        )->from(
            $this->dbHandler->quoteTable('ezcontent_language')
        );

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

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Deletes the language with $id.
     *
     * @param int $id
     */
    public function deleteLanguage($id)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable('ezcontent_language')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );

        $query->prepare()->execute();
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
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcobj_state')
        )->where(
            $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('default_language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask'),
                        $query->bindValue($id, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcobj_state_group')
        )->where(
            $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('default_language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask'),
                        $query->bindValue($id, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcobj_state_group_language')
        )->where(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                0
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcobj_state_language')
        )->where(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                0
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcontentclass')
        )->where(
            $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('initial_language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask'),
                        $query->bindValue($id, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcontentclass_name')
        )->where(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                0
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->where(
            $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('initial_language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask'),
                        $query->bindValue($id, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_attribute')
        )->where(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                0
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_name')
        )->where(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                0
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->where(
            $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('initial_language_id'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask'),
                        $query->bindValue($id, null, \PDO::PARAM_INT)
                    ),
                    0
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        if ($statement->fetchColumn() > 0) {
            return false;
        }

        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('*'), 'count')
        )->from(
            $this->dbHandler->quoteTable('ezurlalias_ml')
        )->where(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('lang_mask'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                ),
                0
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchColumn() == 0;
    }
}
