<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Values\Content\Query\Aggregation;

use DateTimeImmutable;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\Range;
use PHPUnit\Framework\TestCase;

final class RangeTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestToString
     */
    public function testToString(Range $range, string $expected): void
    {
        $this->assertEquals($expected, (string)$range);
    }

    public function dataProviderForTestToString(): iterable
    {
        yield 'empty' => [
            new Range(null, null),
            '[*;*)',
        ];

        yield 'int' => [
            new Range(1, 10),
            '[1;10)',
        ];

        yield 'float' => [
            new Range(0.25, 3.25),
            '[0.25;3.25)',
        ];

        yield 'datetime' => [
            new Range(
                new DateTimeImmutable('2020-01-01T00:00:00+0000'),
                new DateTimeImmutable('2020-12-31T23:59:59+0000'),
            ),
            '[2020-01-01T00:00:00+0000;2020-12-31T23:59:59+0000)',
        ];
    }

    public function testOfInt(): void
    {
        $this->assertEquals(new Range(null, 10), Range::ofInt(null, 10));
        $this->assertEquals(new Range(1, 10), Range::ofInt(1, 10));
        $this->assertEquals(new Range(1, null), Range::ofInt(1, null));
    }

    public function testOfFloat(): void
    {
        $this->assertEquals(new Range(null, 10.0), Range::ofFloat(null, 10.0));
        $this->assertEquals(new Range(1.0, 10.0), Range::ofFloat(1.0, 10.0));
        $this->assertEquals(new Range(1.0, null), Range::ofFloat(1.0, null));
    }

    public function testOfDateTime(): void
    {
        $a = new DateTimeImmutable('2020-01-01T00:00:00+0000');
        $b = new DateTimeImmutable('2020-12-31T23:59:59+0000');

        $this->assertEquals(new Range(null, $b), Range::ofDateTime(null, $b));
        $this->assertEquals(new Range($a, $b), Range::ofDateTime($a, $b));
        $this->assertEquals(new Range($a, null), Range::ofDateTime($a, null));
    }
}
