<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware\Language;

use eZ\Publish\API\Repository\LanguageResolver as APILanguageResolver;
use eZ\Publish\API\Repository\Values\Content\Language;

/**
 * Common abstract implementation of Language resolver.
 *
 * @internal
 */
abstract class AbstractLanguageResolver implements APILanguageResolver
{
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

    /**
     * @param bool $defaultUseAlwaysAvailable
     * @param bool $defaultShowAllTranslations
     */
    public function __construct(
        bool $defaultUseAlwaysAvailable = true,
        bool $defaultShowAllTranslations = false
    ) {
        $this->defaultUseAlwaysAvailable = $defaultUseAlwaysAvailable;
        $this->defaultShowAllTranslations = $defaultShowAllTranslations;
    }

    /**
     * Get list of languages configured via dedicated layer.
     *
     * @return string[]
     */
    abstract protected function getConfiguredLanguages(): array;

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
    final public function setContextLanguage(?string $contextLanguage): void
    {
        $this->contextLanguage = $contextLanguage;
    }

    /**
     * Get context language currently set by custom logic.
     *
     * @return null|string
     */
    final public function getContextLanguage(): ?string
    {
        return $this->contextLanguage;
    }

    /**
     * Get prioritized languages taking into account forced and context languages.
     *
     * @param array|null $forcedLanguages Optional, typically arguments provided to API, will be used first if set.
     *
     * @return array
     */
    final public function getPrioritizedLanguages(?array $forcedLanguages = null): array
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

        return array_values(array_unique(array_merge($languages, $this->getConfiguredLanguages())));
    }

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $defaultUseAlwaysAvailable
     */
    final public function setDefaultUseAlwaysAvailable(bool $defaultUseAlwaysAvailable): void
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
    final public function getUseAlwaysAvailable(?bool $forcedUseAlwaysAvailable = null): bool
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
    final public function setShowAllTranslations(bool $defaultShowAllTranslations): void
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
    final public function getShowAllTranslations(?bool $forcedShowAllTranslations = null): bool
    {
        if ($forcedShowAllTranslations !== null) {
            return $forcedShowAllTranslations;
        }

        return $this->defaultShowAllTranslations;
    }
}
