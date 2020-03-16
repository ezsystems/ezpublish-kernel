<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\UnsupportedSortClauseException;

final class SortClauseParserDispatcher implements SortClauseParserInterface
{
    /** @var \eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface[] */
    private $parsers;

    public function __construct(iterable $parsers = [])
    {
        $this->parsers = $parsers;
    }

    public function parse(SortSpecParserInterface $parser, string $name): SortClause
    {
        $sortClauseParser = $this->findParser($name);
        if ($sortClauseParser instanceof SortClauseParserInterface) {
            return $sortClauseParser->parse($parser, $name);
        }

        throw new UnsupportedSortClauseException($name);
    }

    public function supports(string $name): bool
    {
        return $this->findParser($name) instanceof SortClauseParserInterface;
    }

    private function findParser(string $name): ?SortClauseParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($name)) {
                return $parser;
            }
        }

        return null;
    }
}
