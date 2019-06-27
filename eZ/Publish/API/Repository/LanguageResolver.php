<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

/**
 * Resolve language settings for Repository layer.
 */
interface LanguageResolver
{
    /**
     * For use by custom events / logic setting language for all retrieved objects from repository.
     *
     * If set, user (context) language will be prepended to a list of configured prioritized languages.
     *
     * Languages forced by PHP API consumer when retrieving Repository objects will still take priority,
     * setting both context language and prioritized languages list as a fallback.
     *
     * @param null|string $contextLanguage
     */
    public function setContextLanguage(?string $contextLanguage): void;

    /**
     * Get prioritized languages taking into account forced, context, and configured languages.
     *
     * @param array|null $forcedLanguages Optional, typically arguments provided to API, will be used first if set.
     *
     * @return string[]
     */
    public function getPrioritizedLanguages(?array $forcedLanguages = null): array;

    /**
     * Get currently set UseAlwaysAvailable.
     *
     * @param bool|null $forcedUseAlwaysAvailable Optional, if set will be used instead of configured value,
     *        typically arguments provided to API.
     *
     * @return bool
     */
    public function getUseAlwaysAvailable(?bool $forcedUseAlwaysAvailable = null): bool;

    /**
     * Get currently set showAllTranslations.
     *
     * @param bool|null $forcedShowAllTranslations Optional, if set will be used instead of configured value,
     *        typically arguments provided to API.
     *
     * @return bool
     */
    public function getShowAllTranslations(?bool $forcedShowAllTranslations = null): bool;

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $defaultShowAllTranslations
     */
    public function setShowAllTranslations(bool $defaultShowAllTranslations): void;

    /**
     * For use by event listening to config resolver scope changes (or other event changing configured languages).
     *
     * @param bool $defaultUseAlwaysAvailable
     */
    public function setDefaultUseAlwaysAvailable(bool $defaultUseAlwaysAvailable): void;
}
