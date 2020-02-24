<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

/**
 * @internal
 */
interface SortClausesFactoryInterface
{
    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     *
     * @throws \eZ\Publish\Core\QueryType\BuildIn\SortSpec\Exception\SyntaxErrorException
     */
    public function createFromSpecification(string $specification): array;
}
