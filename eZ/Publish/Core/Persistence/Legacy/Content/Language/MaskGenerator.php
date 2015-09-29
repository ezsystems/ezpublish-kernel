<?php

/**
 * File containing the Language MaskGenerator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Language;

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
     * @param array $languages
     *
     * @return int
     */
    public function generateLanguageMask(array $languages)
    {
        $mask = 0;
        if (isset($languages['always-available'])) {
            $mask |= $languages['always-available'] ? 1 : 0;
            unset($languages['always-available']);
        }

        foreach ($languages as $language => $value) {
            $mask |= $this->languageHandler->loadByLanguageCode($language)->id;
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
    public function isLanguageAlwaysAvailable($language, array $languages)
    {
        return (isset($languages['always-available'])
           && ($languages['always-available'] == $language)
        );
    }

    /**
     * Checks if $languageMask contains the alwaysAvailable bit field.
     *
     * @param int $languageMask
     *
     * @return bool
     */
    public function isAlwaysAvailable($languageMask)
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
    public function removeAlwaysAvailableFlag($languageId)
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
    public function extractLanguageIdsFromMask($languageMask)
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
    public function extractLanguageCodesFromMask($languageMask)
    {
        $languageCodes = array();

        foreach ($this->extractLanguageIdsFromMask($languageMask) as $languageId) {
            $languageCodes[] = $this->languageHandler->load($languageId)->languageCode;
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
    public function isLanguageMaskComposite($languageMask)
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
