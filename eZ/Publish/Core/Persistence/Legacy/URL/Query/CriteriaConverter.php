<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\URL\Query;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

class CriteriaConverter
{
    /**
     * Criterion handlers.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler[]
     */
    protected $handlers;

    /**
     * Construct from an optional array of Criterion handlers.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * Adds handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler $handler
     */
    public function addHandler(CriterionHandler $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Generic converter of criteria into query fragments.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException if Criterion is not applicable to its target
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\URL\Query\Criterion $criterion
     * @return \eZ\Publish\Core\Persistence\Database\Expression|string
     */
    public function convertCriteria(SelectQuery $query, Criterion $criterion)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->accept($criterion)) {
                return $handler->handle($this, $query, $criterion);
            }
        }

        throw new NotImplementedException(
            'No visitor available for: ' . get_class($criterion)
        );
    }
}
