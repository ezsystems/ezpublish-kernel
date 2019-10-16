<?php
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\CompareService as CompareServiceInterface;
use eZ\Publish\API\Repository\LanguageResolver;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

class CompareService implements CompareServiceInterface
{
    /** @var \eZ\Publish\API\Repository\CompareService */
    protected $service;

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    protected $languageResolver;

    public function __construct(
        CompareServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
    }

    public function compareVersions(
        VersionInfo $versionA,
        VersionInfo $versionB,
        ?string $languageCode = null
    ): VersionDiff {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages([$languageCode]);

        return  $this->service->compareVersions($versionA, $versionB, $prioritizedLanguages[0]);
    }
}
