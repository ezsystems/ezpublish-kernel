<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Helper;

class LanguageResolver
{
    protected $languages;

    public function __construct(array $languages)
    {
        $this->languages = $languages;
    }

    public function getLanguages(array $override = [], $fallback = null)
    {
        $languages = empty($override)
            ? $this->languages
            : $override;

        if (!empty($fallback)) {
            $languages[] = $fallback;
        }

        return $languages;
    }
}