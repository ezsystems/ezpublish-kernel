<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn;

use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortSpecLexer;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortSpecParser;

/**
 * @internal
 */
final class SortClausesFactory implements SortClausesFactoryInterface
{
    /** @var \eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface */
    private $sortClauseParser;

    public function __construct(SortClauseParserInterface $sortClauseArgsParser)
    {
        $this->sortClauseParser = $sortClauseArgsParser;
    }

    /**
     * @throws \eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\SyntaxErrorException
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function createFromSpecification(string $specification): array
    {
        $lexer = new SortSpecLexer();
        $lexer->tokenize($specification);

        $parser = new SortSpecParser($this->sortClauseParser, $lexer);

        return $parser->parseSortClausesList();
    }
}
