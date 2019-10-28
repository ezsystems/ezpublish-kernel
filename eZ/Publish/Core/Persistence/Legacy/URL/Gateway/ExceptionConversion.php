<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\URL\Gateway;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\URL\Gateway;
use eZ\Publish\SPI\Persistence\URL\URL;
use Doctrine\DBAL\DBALException;
use PDOException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\URL\Gateway
     */
    protected $innerGateway;

    /**
     * ExceptionConversion constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\URL\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function updateUrl(URL $url)
    {
        try {
            return $this->innerGateway->updateUrl($url);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function find(Criterion $criterion, $offset, $limit, array $sortClauses = [], $doCount = true)
    {
        try {
            return $this->innerGateway->find($criterion, $offset, $limit, $sortClauses, $doCount);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function findUsages($id)
    {
        try {
            return $this->innerGateway->findUsages($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadUrlData($id)
    {
        try {
            return $this->innerGateway->loadUrlData($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadUrlDataByUrl($url)
    {
        try {
            return $this->innerGateway->loadUrlDataByUrl($url);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
