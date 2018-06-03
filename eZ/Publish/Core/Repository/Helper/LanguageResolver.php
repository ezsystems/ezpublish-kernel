<?php

namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Values\Content\Language;

/**
 * Resolves language settings for use in SiteAccess aware Repository.
 */
class LanguageResolver
{
    /**
     * Values typically provided by configuration.
     *
     * These will need to change when configuration (scope) changes using setters below.
     */
    private $configLanguages;
    private $useAlwaysAvailable;
    private $showAllTranslations;

    /**
     * Values typically provided by user context, will need to be set depending on your own custom logic using setter.
     *
     * E.g. Backend UI might expose a language selector for the whole backend that should be reflected on both
     *      UI strings as well as default languages to prioritize for repository objects.
     */
    private $contextLanguage;

    public function __construct(array $configLanguages, bool $useAlwaysAvailable = null, bool $showAllTranslations = null)
    {
        $this->configLanguages = $configLanguages;
        $this->useAlwaysAvailable = $useAlwaysAvailable;
        $this->showAllTranslations = $showAllTranslations;
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param array $configLanguages
     */
    public function setConfigLanguages(array $configLanguages)
    {
        $this->configLanguages = $configLanguages;
    }

    /**
     * For use by custom events / logic setting language for all retrieved objects from repository.
     *
     * User language will, if set, will have prepended before configured languages. But in cases PHP API consumer
     * specifies languages to retrieve repository objects in it will instead be appended as a fallback.
     *
     * @param string|null $contextLanguage
     */
    public function setContextLanguage(?string $contextLanguage)
    {
        $this->contextLanguage = $contextLanguage;
    }

    /**
     * Get prioritized languages taking into account forced-, context- and lastly configured-languages.
     *
     * @param array|null $forcedLanguages Optional, typically arguments provided to API, will be used first if set.
     *
     * @return array|null
     */
    public function getPrioritizedLanguages(?array $forcedLanguages)
    {
        // Skip if languages param has been set by API user
        if ($forcedLanguages !== null) {
            return $forcedLanguages;
        }

        // Detect if we should load all languages by default
        if ($this->showAllTranslations) {
            return Language::ALL;
        }

        // create language based on context and configuration
        $languages = [];
        if ($this->contextLanguage !== null) {
            $languages[] = $this->contextLanguage;
        }

        return array_values(array_unique(array_merge($languages, $this->configLanguages)));
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $useAlwaysAvailable
     */
    public function setUseAlwaysAvailable(bool $useAlwaysAvailable)
    {
        $this->useAlwaysAvailable = $useAlwaysAvailable;
    }

    /**
     * Get currently set UseAlwaysAvailable.
     *
     * @param bool|null $forcedUseAlwaysAvailable Optional, if set will be used instead of configured value,
     *        typically arguments provided to API.
     * @param bool $defaultUseAlwaysAvailable
     *
     * @return bool
     */
    public function getUseAlwaysAvailable(?bool $forcedUseAlwaysAvailable = null, bool $defaultUseAlwaysAvailable = true)
    {
        if ($forcedUseAlwaysAvailable !== null) {
            return $forcedUseAlwaysAvailable;
        }

        if ($this->useAlwaysAvailable !== null) {
            return $this->useAlwaysAvailable;
        }

        return $defaultUseAlwaysAvailable;
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $showAllTranslations
     */
    public function setShowAllTranslations(bool $showAllTranslations)
    {
        $this->showAllTranslations = $showAllTranslations;
    }

    /**
     * Get currently set showAllTranslations.
     *
     * @param bool|null $forcedShowAllTranslations Optional, if set will be used instead of configured value,
     *        typically arguments provided to API.
     * @param bool $defaultShowAllTranslations
     *
     * @return bool
     */
    public function getShowAllTranslations(?bool $forcedShowAllTranslations = null, bool $defaultShowAllTranslations = false)
    {
        if ($forcedShowAllTranslations !== null) {
            return $forcedShowAllTranslations;
        }

        if ($this->showAllTranslations !== null) {
            return $this->showAllTranslations;
        }

        return $defaultShowAllTranslations;
    }
}
