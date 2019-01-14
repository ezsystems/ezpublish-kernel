<?php

/**
 * File containing the Language MaskGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

/**
 * Language MaskGenerator.
 */
class MaskGenerator
{
    /**
     * Language lookup.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new Language MaskGenerator.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(LanguageHandler $languageHandler)
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Generates a language mask from the keys of $languages.
     *
     * @deprecated Move towards using {@see generateLanguageMaskFromLanguageMap()} or the other specific methods.
     *
     * @param array $languages
     *
     * @return int
     */
    public function generateLanguageMask(array $languages)
    {
        return $this->generateLanguageMaskFromLanguageMap($languages);
    }

    /**
     * Generates a language mask from pre-loaded Language Ids.
     *
     * @param int[] $languageIds
     * @param bool $alwaysAvailable
     *
     * @return int
     */
    public function generateLanguageMaskFromLanguageIds(array $languageIds, $alwaysAvailable): int
    {
        // make sure alwaysAvailable part of bit mask always results in 1 or 0
        $languageMask = $alwaysAvailable ? 1 : 0;

        foreach ($languageIds as $languageId) {
            $languageMask |= $languageId;
        }

        return $languageMask;
    }

    /**
     * Generates a language mask from the keys of $languageMap.
     *
     * Typically used for ->name values to get language mask directly from such structure.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language(s) in $languageMap was not be found
     *
     * @param array $languageMap Key values are language code, value is ignored.
     *              Exception if 'always-available' key with language value is set, then always available flag is added.
     *
     * @return int
     */
    public function generateLanguageMaskFromLanguageMap(array $languageMap): int
    {
        $mask = 0;
        if (isset($languageMap['always-available'])) {
            $mask |= $languageMap['always-available'] ? 1 : 0;
            unset($languageMap['always-available']);
        }

        $languageCodes = array_keys($languageMap);
        $languageList = $this->languageHandler->loadListByLanguageCodes($languageCodes);
        foreach ($languageList as $language) {
            $mask |= $language->id;
        }

        if ($missing = array_diff($languageCodes, array_keys($languageList))) {
            throw new NotFoundException('Language', implode(', ', $missing));
        }

        return $mask;
    }

    /**
     * Generates a language mask from plain array of language codes and always available flag.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If language(s) in $languageCodes was not be found
     *
     * @param string[] $languageCodes
     * @param bool $isAlwaysAvailable
     *
     * @return int
     */
    public function generateLanguageMaskFromLanguageCodes(array $languageCodes, bool $isAlwaysAvailable = false): int
    {
        $mask = $isAlwaysAvailable ? 1 : 0;

        $languageList = $this->languageHandler->loadListByLanguageCodes($languageCodes);
        foreach ($languageList as $language) {
            $mask |= $language->id;
        }

        if ($missing = array_diff($languageCodes, array_keys($languageList))) {
            throw new NotFoundException('Language', implode(', ', $missing));
        }

        return $mask;
    }

    /**
     * Generates a language indicator from $languageCode and $alwaysAvailable.
     *
     * @param string $languageCode
     * @param bool $alwaysAvailable
     *
     * @return int
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function generateLanguageIndicator($languageCode, $alwaysAvailable)
    {
        return $this->languageHandler->loadByLanguageCode($languageCode)->id | ($alwaysAvailable ? 1 : 0);
    }

    /**
     * Checks if $language is always available in $languages;.
     *
     * @param string $language
     * @param array $languages
     *
     * @return bool
     */
    public function isLanguageAlwaysAvailable($language, array $languages): bool
    {
        return isset($languages['always-available'])
           && ($languages['always-available'] == $language)
        ;
    }

    /**
     * Checks if $languageMask contains the alwaysAvailable bit field.
     *
     * @param int $languageMask
     *
     * @return bool
     */
    public function isAlwaysAvailable($languageMask): bool
    {
        return (bool)($languageMask & 1);
    }

    /**
     * Removes the alwaysAvailable flag from $languageId and returns cleaned up $languageId.
     *
     * @param int $languageId
     *
     * @return int
     */
    public function removeAlwaysAvailableFlag($languageId): int
    {
        return $languageId & ~1;
    }

    /**
     * Extracts every language Ids contained in $languageMask.
     *
     * @param int $languageMask
     *
     * @return array Array of language Id
     */
    public function extractLanguageIdsFromMask($languageMask): array
    {
        $exp = 2;
        $result = array();

        // Decomposition of $languageMask into its binary components.
        while ($exp <= $languageMask) {
            if ($languageMask & $exp) {
                $result[] = $exp;
            }

            $exp *= 2;
        }

        return $result;
    }

    /**
     * Extracts Language codes contained in given $languageMask.
     *
     * @param int $languageMask
     *
     * @return array
     */
    public function extractLanguageCodesFromMask($languageMask): array
    {
        $languageCodes = [];
        $languageList = $this->languageHandler->loadList(
            $this->extractLanguageIdsFromMask($languageMask)
        );
        foreach ($languageList as $language) {
            $languageCodes[] = $language->languageCode;
        }

        return $languageCodes;
    }

    /**
     * Checks if given $languageMask consists of multiple languages.
     *
     * @param int $languageMask
     *
     * @return bool
     */
    public function isLanguageMaskComposite($languageMask): bool
    {
        // Ignore first bit
        $languageMask = $this->removeAlwaysAvailableFlag($languageMask);

        // Special case
        if ($languageMask === 0) {
            return false;
        }

        // Return false if power of 2
        return (bool)($languageMask & ($languageMask - 1));
    }
}
