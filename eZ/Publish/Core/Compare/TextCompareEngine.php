<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Compare;

use eZ\Publish\API\Repository\CompareEngine;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\DiffStatus;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\CompareResult;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextCompareResult;
use eZ\Publish\SPI\Compare\CompareField;
use SebastianBergmann\Diff\Differ;

class TextCompareEngine implements CompareEngine
{
    /** @var \SebastianBergmann\Diff\Differ */
    private $innerEngine;

    public function __construct()
    {
        $this->innerEngine = new Differ();
    }

    public function compareFieldsData(CompareField $fieldA, CompareField $fieldB): CompareResult
    {
        if ($fieldA->value === null && $fieldB->value === null) {
            return new TextCompareResult([]);
        }

        if ($fieldA->value === $fieldB->value) {
            return new TextCompareResult([
                new StringDiff(
                    $fieldA->value,
                    DiffStatus::UNCHANGED
                ),
            ]);
        }

        if ($fieldA->value === null && $fieldB->value !== null) {
            return new TextCompareResult([
                new StringDiff(
                    $fieldB->value,
                    DiffStatus::ADDED
                ),
            ]);
        }

        if ($fieldA->value !== null && $fieldB->value === null) {
            return new TextCompareResult([
                new StringDiff(
                    $fieldA->value,
                    DiffStatus::REMOVED
                ),
            ]);
        }

        $rawDiff = $this->innerEngine->diffToArray(
            explode(' ', $fieldA->value),
            explode(' ', $fieldB->value)
        );

        $stringDiff = [];
        foreach ($rawDiff as $diff) {
            $stringDiff[] = new StringDiff(
                $diff[0],
                $this->mapStatus($diff[1])
            );
        }

        return new TextCompareResult($stringDiff);
    }

    private function mapStatus(int $status): string
    {
        switch ($status) {
            case 1:
                return DiffStatus::ADDED;
            case 2:
                return DiffStatus::REMOVED;
            default:
                return DiffStatus::UNCHANGED;
        }
    }
}
