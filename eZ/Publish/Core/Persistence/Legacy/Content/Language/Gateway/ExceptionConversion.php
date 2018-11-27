<?php

/**
 * File containing the Language Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;
use eZ\Publish\SPI\Persistence\Content\Language;
use Doctrine\DBAL\DBALException;
use PDOException;
use RuntimeException;

/**
 * Language Handler.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
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
        try {
            return $this->innerGateway->insertLanguage($language);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Updates the data of the given $language.
     *
     * @param Language $language
     */
    public function updateLanguage(Language $language)
    {
        try {
            return $this->innerGateway->updateLanguage($language);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguageListData(array $ids): iterable
    {
        try {
            return $this->innerGateway->loadLanguageListData($ids);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadLanguageListDataByLanguageCode(array $languageCodes): iterable
    {
        try {
            return $this->innerGateway->loadLanguageListDataByLanguageCode($languageCodes);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads the data for all languages.
     *
     * @return string[][]
     */
    public function loadAllLanguagesData()
    {
        try {
            return $this->innerGateway->loadAllLanguagesData();
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes the language with $id.
     *
     * @param int $id
     */
    public function deleteLanguage($id)
    {
        try {
            return $this->innerGateway->deleteLanguage($id);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
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
        try {
            return $this->innerGateway->canDeleteLanguage($id);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
