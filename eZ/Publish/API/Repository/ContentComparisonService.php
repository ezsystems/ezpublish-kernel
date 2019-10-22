<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

interface ContentComparisonService
{
    /**
     * Calculate difference between data in fieldTypes in given Versions.
     *
     * Fields should implement \eZ\Publish\SPI\FieldType\Comparable
     * and be registered with `ezplatform.field_type.comparable` tag to get proper data to ComparisonEngine.
     *
     * Engines should implement \eZ\Publish\SPI\Comparison\ComparisonEngine
     * and be registered with `ezplatform.field_type.comparable.engine`.
     *
     * Only Versions in same language can be compared,
     * if no $languageCode provided, initialLanguageCode ov $versionA is used.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionA
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionB
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff
     */
    public function compareVersions(
        VersionInfo $versionA,
        VersionInfo $versionB,
        ?string $languageCode = null
    ): VersionDiff;
}
