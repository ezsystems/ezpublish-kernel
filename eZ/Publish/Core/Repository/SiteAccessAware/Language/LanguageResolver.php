<?php

/**
 * File containing LanguageResolver class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware\Language;

/**
 * Resolves language settings for use in SiteAccess aware Repository.
 */
final class LanguageResolver extends AbstractLanguageResolver
{
    /**
     * Values typically provided by configuration.
     *
     * @var string[]
     */
    private $configLanguages;

    public function __construct(
        array $configLanguages,
        bool $defaultUseAlwaysAvailable = true,
        bool $defaultShowAllTranslations = false
    ) {
        $this->configLanguages = $configLanguages;
        parent::__construct($defaultUseAlwaysAvailable, $defaultShowAllTranslations);
    }

    protected function getConfiguredLanguages(): array
    {
        return $this->configLanguages;
    }
}
