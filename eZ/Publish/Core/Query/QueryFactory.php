<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Query;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;

final class QueryFactory implements QueryFactoryInterface
{
    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(QueryTypeRegistry $queryTypeRegistry)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function create(string $type, array $parameters = []): Query
    {
        return $this->queryTypeRegistry->getQueryType($type)->getQuery($parameters);
    }
}
