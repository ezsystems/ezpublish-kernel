<?php

/**
 * ContentService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Decorator\ContentServiceDecorator;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;

/**
 * SiteAccess aware implementation of ContentService injecting languages where needed.
 */
class ContentService extends ContentServiceDecorator
{
    /** @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\ContentService $service
     * @param \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver $languageResolver
     */
    public function __construct(
        ContentServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        parent::__construct($service);

        $this->languageResolver = $languageResolver;
    }

    public function loadContentByContentInfo(ContentInfo $contentInfo, array $languages = null, $versionNo = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContentByContentInfo(
            $contentInfo,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContentByVersionInfo(VersionInfo $versionInfo, array $languages = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContentByVersionInfo(
            $versionInfo,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContent($contentId, array $languages = null, $versionNo = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContent(
            $contentId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContentByRemoteId($remoteId, array $languages = null, $versionNo = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContentByRemoteId(
            $remoteId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }
}
