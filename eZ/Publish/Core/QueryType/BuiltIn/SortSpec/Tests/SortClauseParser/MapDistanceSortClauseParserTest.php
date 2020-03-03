<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Tests\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\MapLocationDistance;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParser\MapDistanceSortClauseParser;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortSpecParserInterface;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Token;
use PHPUnit\Framework\TestCase;

final class MapDistanceSortClauseParserTest extends TestCase
{
    private const EXAMPLE_CONTENT_TYPE_ID = 'place';
    private const EXAMPLE_FIELD_ID = 'location';
    private const EXAMPLE_LAT = 50.0647;
    private const EXAMPLE_LON = 19.9450;

    /** @var \eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParser\MapDistanceSortClauseParser */
    private $mapDistanceSortClauseParser;

    protected function setUp(): void
    {
        $this->mapDistanceSortClauseParser = new MapDistanceSortClauseParser();
    }

    public function testParse(): void
    {
        $parser = $this->createMock(SortSpecParserInterface::class);
        $parser
            ->method('match')
            ->withConsecutive(
                [Token::TYPE_ID],
                [Token::TYPE_DOT],
                [Token::TYPE_ID],
                [Token::TYPE_FLOAT],
                [Token::TYPE_FLOAT]
            )
            ->willReturnOnConsecutiveCalls(
                new Token(Token::TYPE_ID, self::EXAMPLE_CONTENT_TYPE_ID),
                new Token(Token::TYPE_DOT),
                new Token(Token::TYPE_ID, self::EXAMPLE_FIELD_ID),
                new Token(Token::TYPE_FLOAT, (string)self::EXAMPLE_LAT),
                new Token(Token::TYPE_FLOAT, (string)self::EXAMPLE_LON)
            );

        $parser->method('parseSortDirection')->willReturn(Query::SORT_ASC);

        $this->assertEquals(
            new MapLocationDistance(
                self::EXAMPLE_CONTENT_TYPE_ID,
                self::EXAMPLE_FIELD_ID,
                self::EXAMPLE_LAT,
                self::EXAMPLE_LON,
                Query::SORT_ASC
            ),
            $this->mapDistanceSortClauseParser->parse($parser, 'map_distance')
        );
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->mapDistanceSortClauseParser->supports('map_distance'));
    }
}
