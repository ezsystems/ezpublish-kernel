<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\CompareService;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

abstract class CompareServiceDecorator implements CompareService
{
    /** @var \eZ\Publish\API\Repository\CompareService */
    protected $innerService;

    public function __construct(CompareService $innerService)
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
