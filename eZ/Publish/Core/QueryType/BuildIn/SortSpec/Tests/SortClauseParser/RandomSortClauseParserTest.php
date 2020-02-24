<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec\Tests\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Random;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseParser\RandomSortClauseParser;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortSpecParserInterface;
use eZ\Publish\Core\QueryType\BuildIn\SortSpec\Token;
use PHPUnit\Framework\TestCase;

final class RandomSortClauseParserTest extends TestCase
{
    private const EXAMPLE_SEED = 1;

    /** @var \eZ\Publish\Core\QueryType\BuildIn\SortSpec\SortClauseParser\RandomSortClauseParser */
    private $randomSortClauseParser;

    protected function setUp(): void
    {
        $this->randomSortClauseParser = new RandomSortClauseParser();
    }

    public function testParse(): void
    {
        $parser = $this->createMock(SortSpecParserInterface::class);
        $parser
            ->method('isNextToken')
            ->with(Token::TYPE_INT)
            ->willReturn(true);

        $parser
            ->method('match')
            ->with(Token::TYPE_INT)
            ->willReturn(new Token(Token::TYPE_INT, (string)self::EXAMPLE_SEED));

        $parser->method('parseSortDirection')->willReturn(Query::SORT_ASC);

        $this->assertEquals(
            new Random(self::EXAMPLE_SEED, Query::SORT_ASC),
            $this->randomSortClauseParser->parse($parser, 'random')
        );
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->randomSortClauseParser->supports('random'));
    }
}
