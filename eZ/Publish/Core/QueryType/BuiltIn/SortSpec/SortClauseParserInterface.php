<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

interface SortClauseParserInterface
{
    /**
     * @throws \eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\UnsupportedSortClauseException If sort clause is not supported by parser
     */
    public function parse(SortSpecParserInterface $parser, string $name): SortClause;

    public function supports(string $name): bool;
}
