<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Exception\SyntaxErrorException;

/**
 * Parser recognizing the following grammar:.
 *
 *    <sort-clauses-list> ::= <sort-clause> ("," <sort-clause>)?
 *    <sort-clause> ::= <id> <sort-clause-args>? <sort-direction>?
 *    <sort-clause-args> ::= <sort-clause-field-args> | <sort-clause-map-distance-args> | <sort-clause-random-args>
 *    <sort-clause-field-args> ::= <id> "." <id>
 *    sort-clause-map-distance-args> ::=  <id> "." <id> <float> <float>
 *    <sort-clause-random-args> ::= <int>?
 *    <sort-clause-sort-direction> ::= "asc" | "desc"
 *
 *    <id> ::= [a-zA-Z_][a-zA-Z0-9_]*
 *    <float> ::= -?[0-9]+\.[0-9]+
 *    <int> ::= -?[0-9]+
 */
final class SortSpecParser implements SortSpecParserInterface
{
    private const DEFAULT_SORT_DIRECTION = Query::SORT_ASC;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecLexerInterface */
    private $lexer;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseParserInterface */
    private $sortClauseParser;

    public function __construct(SortClauseParserInterface $sortClauseParser, SortSpecLexerInterface $lexer = null)
    {
        if ($lexer === null) {
            $lexer = new SortSpecLexer();
        }

        $this->sortClauseParser = $sortClauseParser;
        $this->lexer = $lexer;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function parseSortClausesList(): array
    {
        $sortClauses = [];
        while (!$this->lexer->isEOF()) {
            $sortClauses[] = $this->parseSortClause();
            if ($this->isNextToken(Token::TYPE_COMMA)) {
                $this->match(Token::TYPE_COMMA);
            }
        }

        return $sortClauses;
    }

    public function parseSortClause(): SortClause
    {
        $name = $this->match(Token::TYPE_ID)->getValue();

        return $this->sortClauseParser->parse($this, $name);
    }

    public function parseSortDirection(): string
    {
        if ($this->isNextToken(Token::TYPE_ASC, Token::TYPE_DESC)) {
            $token = $this->matchAnyOf(Token::TYPE_ASC, Token::TYPE_DESC);

            switch ($token->getType()) {
                case Token::TYPE_ASC:
                    return Query::SORT_ASC;
                case Token::TYPE_DESC:
                    return Query::SORT_DESC;
            }
        }

        return self::DEFAULT_SORT_DIRECTION;
    }

    public function isNextToken(string ...$types): bool
    {
        $nextToken = $this->lexer->peek();

        if ($nextToken !== null) {
            foreach ($types as $type) {
                if ($nextToken->isA($type)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function match(string $type): Token
    {
        return $this->matchAnyOf($type);
    }

    public function matchAnyOf(string ...$types): Token
    {
        if ($this->isNextToken(...$types)) {
            return $this->lexer->consume();
        }

        throw SyntaxErrorException::fromUnexpectedToken(
            $this->lexer->getInput(),
            $this->lexer->peek(),
            $types
        );
    }
}
