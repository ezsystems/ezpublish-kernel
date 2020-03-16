<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\UnsupportedSortClauseException;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortSpecParserInterface;

/**
 * Parser for sort clauses which expect only sort direction in constructor parameter.
 */
final class DefaultSortClauseParser implements SortClauseParserInterface
{
    /** @var string[] */
    private $valueObjectClassMap;

    public function __construct(array $valueObjectClassMap)
    {
        $this->valueObjectClassMap = $valueObjectClassMap;
    }

    public function parse(SortSpecParserInterface $parser, string $name): SortClause
    {
        if (isset($this->valueObjectClassMap[$name])) {
            $class = $this->valueObjectClassMap[$name];

            return new $class($parser->parseSortDirection());
        }

        throw new UnsupportedSortClauseException($name);
    }

    public function supports(string $name): bool
    {
        return isset($this->valueObjectClassMap[$name]);
    }
}
