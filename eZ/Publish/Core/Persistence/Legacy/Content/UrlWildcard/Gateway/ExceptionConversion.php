<?php

/**
 * File containing the Section Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * UrlAlias Handler.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Inserts the given UrlWildcard.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $urlWildcard
     *
     * @return mixed
     */
    public function insertUrlWildcard(UrlWildcard $urlWildcard)
    {
        try {
            return $this->innerGateway->insertUrlWildcard($urlWildcard);
        } catch (DBALException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Deletes the UrlWildcard with given $id.
     *
     * @param mixed $id
     */
    public function deleteUrlWildcard($id)
    {
        try {
            return $this->innerGateway->deleteUrlWildcard($id);
        } catch (DBALException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * @param mixed $parentId
     *
     * @return array
     */
    public function loadUrlWildcardData($parentId)
    {
        try {
            return $this->innerGateway->loadUrlWildcardData($parentId);
        } catch (DBALException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads an array with data about UrlWildcards (paged).
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    public function loadUrlWildcardsData($offset = 0, $limit = -1)
    {
        try {
            return $this->innerGateway->loadUrlWildcardsData($offset, $limit);
        } catch (DBALException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads the UrlWildcard with given $sourceUrl.
     *
     * @param string $sourceUrl
     *
     * @return array
     */
    public function loadUrlWildcardBySourceUrl(string $sourceUrl): array
    {
        try {
            return $this->innerGateway->loadUrlWildcardBySourceUrl($sourceUrl);
        } catch (DBALException | PDOException $e) {
            throw new \RuntimeException('Database error', 0, $e);
        }
    }
}
