<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Locale;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use eZ\Publish\API\Repository\UserPreferenceService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

class UserLanguagePreferenceProvider implements UserLanguagePreferenceProviderInterface
{
    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \eZ\Publish\API\Repository\UserPreferenceService */
    private $userPreferenceService;

    /** @var array */
    private $languageCodesMap;

    /** @var string */
    private $localeFallback;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \eZ\Publish\API\Repository\UserPreferenceService $userPreferenceService
     * @param array $languageCodesMap
     * @param string $localeFallback
     */
    public function __construct(
        RequestStack $requestStack,
        UserPreferenceService $userPreferenceService,
        array $languageCodesMap,
        string $localeFallback
    ) {
        $this->requestStack = $requestStack;
        $this->userPreferenceService = $userPreferenceService;
        $this->languageCodesMap = $languageCodesMap;
        $this->localeFallback = $localeFallback;
    }

    public function getPreferredLocales(Request $request = null): array
    {
        $languages = [$this->localeFallback];

        $request = $request ?? $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $browserLanguages = $request->getLanguages();
            if ([] !== $browserLanguages) {
                $languages = $browserLanguages;
            }
        }

        try {
            $preferredLanguage = $this->userPreferenceService->getUserPreference('language')->value;
            array_unshift($languages, $preferredLanguage);
        } catch (NotFoundException $e) {
        }

        return array_unique($languages);
    }

    public function getPreferredLanguages(): array
    {
        $languageCodes = [[]];
        foreach ($this->getPreferredLocales() as $locale) {
            $locale = strtolower($locale);
            if (!isset($this->languageCodesMap[$locale])) {
                continue;
            }

            $languageCodes[] = $this->languageCodesMap[$locale];
        }

        return array_unique(array_merge(...$languageCodes));
    }
}
