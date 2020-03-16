<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Pagination\Pagerfanta\AdapterFactory;

use eZ\Publish\API\Repository\Values\Content\Query;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @internal
 */
interface SearchHitAdapterFactoryInterface
{
    public function createAdapter(Query $query, array $languageFilter = []): AdapterInterface;

    public function createFixedAdapter(Query $query, array $languageFilter = []): AdapterInterface;
}
