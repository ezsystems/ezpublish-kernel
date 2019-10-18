<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\ContentComparisonService;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

abstract class ContentComparisonServiceDecorator implements ContentComparisonService
{
    /** @var \eZ\Publish\API\Repository\ContentComparisonService */
    protected $innerService;

    public function __construct(ContentComparisonService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function compareVersions(
        VersionInfo $versionA,
        VersionInfo $versionB,
        ?string $languageCode = null
    ): VersionDiff {
        return $this->innerService->compareVersions($versionA, $versionB, $languageCode);
    }
}
