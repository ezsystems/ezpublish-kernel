<?php

/**
 * File containing the DoctrineDatabase criteria converter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
class CriteriaConverter
{
    /**
     * Criterion handlers.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler[]
     */
    protected $handlers;

    /**
     * Construct from an optional array of Criterion handlers.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * Adds handler.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler $handler
     */
    public function addHandler(CriterionHandler $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Generic converter of criteria into query fragments.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function convertCriteria(
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        foreach ($this->handlers as $handler) {
            if ($handler->accept($criterion)) {
                return $handler->handle($this, $query, $criterion, $languageSettings);
            }
        }

        throw new NotImplementedException(
            'No visitor available for: ' . get_class($criterion) . ' with operator ' . $criterion->operator
        );
    }
}
