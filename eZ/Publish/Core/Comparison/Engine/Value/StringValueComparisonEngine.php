<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Comparison\Engine\Value;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\DiffStatus;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff;
use eZ\Publish\SPI\Comparison\Value\StringComparisonValue;
use SebastianBergmann\Diff\Differ;

final class StringValueComparisonEngine
{
    /** @var \SebastianBergmann\Diff\Differ */
    private $innerEngine;

    public function __construct()
    {
        $this->innerEngine = new Differ();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff[]
     */
    public function compareValues(StringComparisonValue $stringA, StringComparisonValue $stringB): array
    {
        if ($stringA->value === $stringB->value) {
            return [
                new StringDiff(
                    $stringA->value,
                    DiffStatus::UNCHANGED
                ),
            ];
        }

        if ($stringA->value === null && $stringB->value !== null) {
            return [
                new StringDiff(
                    $stringB->value,
                    DiffStatus::ADDED
                ),
            ];
        }

        if ($stringA->value !== null && $stringB->value === null) {
            return [
                new StringDiff(
                    $stringA->value,
                    DiffStatus::REMOVED
                ),
            ];
        }

        $rawDiff = $this->innerEngine->diffToArray(
            explode(' ', $stringA->value),
            explode(' ', $stringB->value)
        );

        $stringDiff = [];
        foreach ($rawDiff as $diff) {
            $stringDiff[] = new StringDiff(
                $diff[0],
                $this->mapStatus($diff[1])
            );
        }

        return $stringDiff;
    }

    private function mapStatus(int $status): string
    {
        switch ($status) {
            case Differ::ADDED:
                return DiffStatus::ADDED;
            case Differ::REMOVED:
                return DiffStatus::REMOVED;
            default:
                return DiffStatus::UNCHANGED;
        }
    }
}
