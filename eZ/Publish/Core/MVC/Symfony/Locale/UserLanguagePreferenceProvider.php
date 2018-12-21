<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Locale;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UserLanguagePreferenceProvider implements UserLanguagePreferenceProviderInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var array
     */
    private $languageCodesMap;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param array $languageCodesMap
     */
    public function __construct(RequestStack $requestStack, array $languageCodesMap)
    {
        $this->requestStack = $requestStack;
        $this->languageCodesMap = $languageCodesMap;
    }

    public function getPreferredLocales(Request $request = null): array
    {
        $request = $request ?? $this->requestStack->getCurrentRequest();
        $preferredLocales = $request->headers->get('Accept-Language') ?? '';
        $preferredLocales = array_reduce(
            explode(',', $preferredLocales),
            function (array $result, string $languageWithQuality) {
                [$language, $quality] = array_merge(explode(';q=', $languageWithQuality), [1]);
                $result[$language] = (float) $quality;

                return $result;
            },
            []
        );
        arsort($preferredLocales);

        return array_keys($preferredLocales);
    }

    public function getPreferredLanguages(): array
    {
        $languageCodes = [];
        foreach ($this->getPreferredLocales() as $locale) {
            $locale = strtolower($locale);
            if (isset($this->languageCodesMap[$locale])) {
                $languageCodes = array_merge($languageCodes, $this->languageCodesMap[$locale]);
            } elseif (preg_match('/^([a-z]{3})-([a-z]{2})$/', $locale, $matches)) {
                // if the given locale is already in the eZ format
                $languageCodes[] = strtolower($matches[1]) . '-' . strtoupper($matches[2]);
            }
        }

        return array_unique($languageCodes);
    }
}
