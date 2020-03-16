<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Tests;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\UnsupportedSortClauseException;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserDispatcher;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortSpecParserInterface;
use PHPUnit\Framework\TestCase;

final class SortClauseParserDispatcherTest extends TestCase
{
    private const EXAMPLE_SORT_CLAUSE = 'content_id';

    public function testParse(): void
    {
        $sortSpecParser = $this->createMock(SortSpecParserInterface::class);
        $sortClause = $this->createMock(SortClause::class);

        $parser = $this->createMock(SortClauseParserInterface::class);
        $parser->method('supports')->with(self::EXAMPLE_SORT_CLAUSE)->willReturn(true);
        $parser->method('parse')->with($sortSpecParser, self::EXAMPLE_SORT_CLAUSE)->willReturn($sortClause);

        $dispatcher = new SortClauseParserDispatcher([$parser]);

        $this->assertEquals(
            $sortClause,
            $dispatcher->parse($sortSpecParser, self::EXAMPLE_SORT_CLAUSE)
        );
    }

    public function testParseThrowsUnsupportedSortClauseException(): void
    {
        $this->expectException(UnsupportedSortClauseException::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find %s for %s sort clause',
            SortClauseParserInterface::class,
            self::EXAMPLE_SORT_CLAUSE
        ));

        $parser = $this->createMock(SortClauseParserInterface::class);
        $parser->method('supports')->with(self::EXAMPLE_SORT_CLAUSE)->willReturn(false);

        $dispatcher = new SortClauseParserDispatcher([$parser]);
        $dispatcher->parse($this->createMock(SortSpecParserInterface::class), self::EXAMPLE_SORT_CLAUSE);
    }

    public function testSupports(): void
    {
        $parser = $this->createMock(SortClauseParserInterface::class);
        $parser->method('supports')->with(self::EXAMPLE_SORT_CLAUSE)->willReturn(true);

        $dispatcher = new SortClauseParserDispatcher([$parser]);

        $this->assertTrue($dispatcher->supports(self::EXAMPLE_SORT_CLAUSE));
    }
}
