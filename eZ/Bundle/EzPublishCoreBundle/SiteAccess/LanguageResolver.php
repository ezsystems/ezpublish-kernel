<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\AbstractLanguageResolver;

/**
 * Resolves language settings for use in SiteAccess aware Repository.
 */
final class LanguageResolver extends AbstractLanguageResolver
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver,
        bool $defaultUseAlwaysAvailable = true,
        bool $defaultShowAllTranslations = false
    ) {
        $this->configResolver = $configResolver;
        parent::__construct($defaultUseAlwaysAvailable, $defaultShowAllTranslations);
    }

    /**
     * Get list of languages configured via scope/SiteAccess context.
     *
     * @return string[]
     */
    protected function getConfiguredLanguages(): array
    {
        return $this->configResolver->getParameter('languages');
    }
}
