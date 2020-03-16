<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Tests\SortClauseParser;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\Exception\UnsupportedSortClauseException;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParser\DefaultSortClauseParser;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParserInterface;
use eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortSpecParserInterface;
use PHPUnit\Framework\TestCase;

final class DefaultSortClauseParserTest extends TestCase
{
    /** @var \eZ\Publish\Core\QueryType\BuiltIn\SortSpec\SortClauseParser\DefaultSortClauseParser */
    private $defaultSortClauseParser;

    protected function setUp(): void
    {
        $this->defaultSortClauseParser = new DefaultSortClauseParser([
            'depth' => Location\Depth::class,
            'priority' => Location\Priority::class,
            'id' => Location\Id::class,
        ]);
    }

    public function testParse(): void
    {
        $parser = $this->createMock(SortSpecParserInterface::class);
        $parser->method('parseSortDirection')->willReturn(Query::SORT_ASC);

        $this->assertEquals(
            new Location\Depth(Query::SORT_ASC),
            $this->defaultSortClauseParser->parse($parser, 'depth')
        );

        $this->assertEquals(
            new Location\Priority(Query::SORT_ASC),
            $this->defaultSortClauseParser->parse($parser, 'priority')
        );
    }

    public function testParseThrowsUnsupportedSortClauseException(): void
    {
        $this->expectException(UnsupportedSortClauseException::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find %s for unsupported sort clause',
            SortClauseParserInterface::class
        ));

        $this->defaultSortClauseParser->parse(
            $this->createMock(SortSpecParserInterface::class),
            'unsupported'
        );
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->defaultSortClauseParser->supports('depth'));
        $this->assertTrue($this->defaultSortClauseParser->supports('priority'));
        $this->assertTrue($this->defaultSortClauseParser->supports('id'));

        $this->assertFalse($this->defaultSortClauseParser->supports('unsupported'));
    }
}
