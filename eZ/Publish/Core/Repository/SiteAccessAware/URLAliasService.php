<?php

/**
 * URLAliasService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Decorator\URLAliasServiceDecorator;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;

/**
 * SiteAccess aware implementation of URLAliasService injecting languages where needed.
 */
class URLAliasService extends URLAliasServiceDecorator
{
    /** @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\URLAliasService $service
     * @param \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver $languageResolver
     */
    public function __construct(
        URLAliasServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        parent::__construct($service);

        $this->languageResolver = $languageResolver;
    }

    public function listLocationAliases(
        Location $location,
        $custom = true,
        $languageCode = null,
        bool $showAllTranslations = null,
        array $prioritizedLanguages = null
    ) {
        return $this->service->listLocationAliases(
            $location,
            $custom,
            $languageCode,
            $this->languageResolver->getShowAllTranslations($showAllTranslations),
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function reverseLookup(
        Location $location,
        $languageCode = null,
        bool $showAllTranslations = null,
        array $prioritizedLanguages = null
    ) {
        return $this->service->reverseLookup(
            $location,
            $languageCode,
            $this->languageResolver->getShowAllTranslations($showAllTranslations),
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }
}
