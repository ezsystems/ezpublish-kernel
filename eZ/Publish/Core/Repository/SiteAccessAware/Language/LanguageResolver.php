<?php

/**
 * File containing LanguageResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware\Language;

use eZ\Publish\API\Repository\Values\Content\Language;

/**
 * Resolves language settings for use in SiteAccess aware Repository.
 */
class LanguageResolver
{
    /**
     * Values typically provided by configuration.
     *
     * These will need to change when configuration (scope) changes mid flight using setters below.
     *
     * @var array
     */
    private $configLanguages;

    /** @var bool */
    private $defaultUseAlwaysAvailable;

    /** @var bool */
    private $defaultShowAllTranslations;

    /**
     * Values typically provided by user context, will need to be set depending on your own custom logic using setter.
     *
     * E.g. Backend UI might expose a language selector for the whole backend that should be reflected on both
     *      UI strings as well as default languages to prioritize for repository objects.
     *
     * If set, this will have priority over configured languages.
     *
     * @var string|null
     */
    private $contextLanguage;

    public function __construct(
        array $configLanguages,
        bool $defaultUseAlwaysAvailable = true,
        bool $defaultShowAllTranslations = false
    ) {
        $this->configLanguages = $configLanguages;
        $this->defaultUseAlwaysAvailable = $defaultUseAlwaysAvailable;
        $this->defaultShowAllTranslations = $defaultShowAllTranslations;
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param array $configLanguages
     */
    public function setConfigLanguages(array $configLanguages): void
    {
        $this->configLanguages = $configLanguages;
    }

    /**
     * For use by custom events / logic setting language for all retrieved objects from repository.
     *
     * User language will, if set, will have prepended before configured languages. But in cases PHP API consumer
     * specifies languages to retrieve repository objects in it will instead be appended as a fallback.
     *
     * If set, this will have priority over configured languages.
     *
     * @param string|null $contextLanguage
     */
    public function setContextLanguage(?string $contextLanguage): void
    {
        $this->contextLanguage = $contextLanguage;
    }

    /**
     * Get prioritized languages taking into account forced-, context- and lastly configured-languages.
     *
     * @param array|null $forcedLanguages Optional, typically arguments provided to API, will be used first if set.
     *
     * @return array
     */
    public function getPrioritizedLanguages(?array $forcedLanguages): array
    {
        // Skip if languages param has been set by API user
        if ($forcedLanguages !== null) {
            return $forcedLanguages;
        }

        // Detect if we should load all languages by default
        if ($this->defaultShowAllTranslations) {
            return Language::ALL;
        }

        // create language based on context and configuration, where context language is made most important one
        $languages = [];
        if ($this->contextLanguage !== null) {
            $languages[] = $this->contextLanguage;
        }

        return array_values(array_unique(array_merge($languages, $this->configLanguages)));
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $defaultUseAlwaysAvailable
     */
    public function setDefaultUseAlwaysAvailable(bool $defaultUseAlwaysAvailable): void
    {
        $this->defaultUseAlwaysAvailable = $defaultUseAlwaysAvailable;
    }

    /**
     * Get currently set UseAlwaysAvailable.
     *
     * @param bool|null $forcedUseAlwaysAvailable Optional, if set will be used instead of configured value,
     *        typically arguments provided to API.
     *
     * @return bool
     */
    public function getUseAlwaysAvailable(?bool $forcedUseAlwaysAvailable = null): bool
    {
        if ($forcedUseAlwaysAvailable !== null) {
            return $forcedUseAlwaysAvailable;
        }

        return $this->defaultUseAlwaysAvailable;
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $defaultShowAllTranslations
     */
    public function setShowAllTranslations(bool $defaultShowAllTranslations): void
    {
        $this->defaultShowAllTranslations = $defaultShowAllTranslations;
    }

    /**
     * Get currently set showAllTranslations.
     *
     * @param bool|null $forcedShowAllTranslations Optional, if set will be used instead of configured value,
     *        typically arguments provided to API.
     *
     * @return bool
     */
    public function getShowAllTranslations(?bool $forcedShowAllTranslations = null): bool
    {
        if ($forcedShowAllTranslations !== null) {
            return $forcedShowAllTranslations;
        }

        return $this->defaultShowAllTranslations;
    }
}
