<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Compare\Tests;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\DiffStatus;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextCompareResult;
use eZ\Publish\Core\Compare\TextCompareEngine;
use eZ\Publish\SPI\Compare\CompareField;
use eZ\Publish\SPI\Compare\Field\TextCompareField;
use PHPUnit\Framework\TestCase;

class TextCompareEngineTest extends TestCase
{
    /** @var \eZ\Publish\Core\Compare\TextCompareEngine */
    private $engine;

    protected function setUp(): void
    {
        $this->engine = new TextCompareEngine();
    }

    public function fieldsAndResultProvider()
    {
        return [
            [
                new TextCompareField(['value' => 'No Change Value']),
                new TextCompareField(['value' => 'No Change Value']),
                new TextCompareResult([
                    new StringDiff(
                        'No Change Value',
                        DiffStatus::UNCHANGED
                    ),
                ]),
            ],
            [
                new TextCompareField(['value' => null]),
                new TextCompareField(['value' => 'Added Value']),
                new TextCompareResult([
                    new StringDiff(
                        'Added Value',
                        DiffStatus::ADDED
                    ),
                ]),
            ],
            [
                new TextCompareField(['value' => 'Removed Value']),
                new TextCompareField(['value' => null]),
                new TextCompareResult([
                    new StringDiff(
                        'Removed Value',
                        DiffStatus::REMOVED
                    ),
                ]),
            ],
            [
                new TextCompareField(['value' => null]),
                new TextCompareField(['value' => null]),
                new TextCompareResult([]),
            ],
            [
                new TextCompareField(['value' => 'unchanged removed']),
                new TextCompareField(['value' => 'unchanged added']),
                new TextCompareResult([
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
    public function testCompareFieldsData(CompareField $fieldA, CompareField $fieldB, TextCompareResult $expected)
    {
        $this->assertEquals(
            $expected,
            $this->engine->compareFieldsData($fieldA, $fieldB)
        );
    }
}
