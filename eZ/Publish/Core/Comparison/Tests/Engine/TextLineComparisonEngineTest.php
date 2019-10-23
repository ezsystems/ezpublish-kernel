<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison\Engine\Tests;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\DiffStatus;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextLineComparisonResult;
use eZ\Publish\Core\Comparison\Engine\FieldType\TextLineComparisonEngine;
use eZ\Publish\Core\Comparison\Engine\Value\StringValueComparisonEngine;
use eZ\Publish\SPI\Comparison\ComparisonResult;
use eZ\Publish\SPI\Comparison\ComparisonValue;
use eZ\Publish\SPI\Comparison\Field\TextLine;
use eZ\Publish\SPI\Comparison\Value\StringComparisonValue;
use PHPUnit\Framework\TestCase;

class TextLineComparisonEngineTest extends TestCase
{
    /** @var \eZ\Publish\Core\Comparison\Engine\FieldType\TextLineComparisonEngine */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new TextLineComparisonEngine(
            new StringValueComparisonEngine()
        );
    }

    public function fieldsAndResultProvider()
    {
        return [
            [
                new StringComparisonValue(['value' => 'No Change Value']),
                new StringComparisonValue(['value' => 'No Change Value']),
                new TextLineComparisonResult([
                    new StringDiff(
                        'No Change Value',
                        DiffStatus::UNCHANGED
                    ),
                ]),
            ],
            [
                new StringComparisonValue(['value' => null]),
                new StringComparisonValue(['value' => 'Added Value']),
                new TextLineComparisonResult([
                    new StringDiff(
                        'Added Value',
                        DiffStatus::ADDED
                    ),
                ]),
            ],
            [
                new StringComparisonValue(['value' => 'Removed Value']),
                new StringComparisonValue(['value' => null]),
                new TextLineComparisonResult([
                    new StringDiff(
                        'Removed Value',
                        DiffStatus::REMOVED
                    ),
                ]),
            ],
            [
                new StringComparisonValue(['value' => null]),
                new StringComparisonValue(['value' => null]),
                new TextLineComparisonResult([
                    new StringDiff(
                        null,
                        DiffStatus::UNCHANGED
                    ),
                ]),
            ],
            [
                new StringComparisonValue(['value' => 'unchanged removed']),
                new StringComparisonValue(['value' => 'unchanged added']),
                new TextLineComparisonResult([
                    new StringDiff(
                        'unchanged',
                        DiffStatus::UNCHANGED
                    ),
                    new StringDiff(
                        'removed',
                        DiffStatus::REMOVED
                    ),
                    new StringDiff(
                        'added',
                        DiffStatus::ADDED
                    ),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider fieldsAndResultProvider
     */
    public function testCompareFieldsData(ComparisonValue $fieldA, ComparisonValue $fieldB, ComparisonResult $expected)
    {
        $this->assertEquals(
            $expected,
            $this->engine->compareFieldsData(
                new TextLine(['textLine' => $fieldA]),
                new TextLine(['textLine' => $fieldB]),
            )
        );
    }
}
