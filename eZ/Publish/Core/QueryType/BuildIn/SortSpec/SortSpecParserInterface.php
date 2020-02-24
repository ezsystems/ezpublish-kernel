<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

interface SortSpecParserInterface
{
    public function parseSortClausesList(): array;

    public function parseSortClause(): SortClause;

    public function parseSortDirection(): string;

    public function isNextToken(string ...$types): bool;

    public function match(string $type): Token;

    public function matchAnyOf(string ...$types): Token;
}
