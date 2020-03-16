<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway;
use eZ\Publish\SPI\Persistence\Content\Language;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * @internal Internal exception conversion layer.
 */
final class ExceptionConversion extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway
     */
    private $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function insertLanguage(Language $language): int
    {
        try {
            return $this->innerGateway->insertLanguage($language);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateLanguage(Language $language): void
    {
        try {
            $this->innerGateway->updateLanguage($language);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadLanguageListData(array $ids): iterable
    {
        try {
            return $this->innerGateway->loadLanguageListData($ids);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadLanguageListDataByLanguageCode(array $languageCodes): iterable
    {
        try {
            return $this->innerGateway->loadLanguageListDataByLanguageCode($languageCodes);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllLanguagesData(): array
    {
        try {
            return $this->innerGateway->loadAllLanguagesData();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteLanguage(int $id): void
    {
        try {
            $this->innerGateway->deleteLanguage($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function canDeleteLanguage(int $id): bool
    {
        try {
            return $this->innerGateway->canDeleteLanguage($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
