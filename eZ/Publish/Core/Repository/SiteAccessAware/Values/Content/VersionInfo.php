<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Values\Content;

use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;

/**
 * @property-read string $name Name of the current version in a specific language (@see VersionInfo::$languageCode)
 * @property-read string $languageCode The language code of the version (@see VersionInfo::$name)
 */
class VersionInfo extends APIVersionInfo
{
    /**
     * The name in a preferred language of current SiteAccess (with a fallback to main language).
     *
     * @var string
     */
    protected $name;

    /**
     * The language code of content Version name.
     *
     * @var string
     */
    protected $languageCode;

    /**
     * Content info
     *
     * @var ContentInfo
     */
    protected $contentInfo;

    /**
     * Content of the content this version belongs to.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

    /**
     * @deprecated
     *
     * Returns the names computed from the name schema in the available languages.
     *
     * @return string[]
     */
    public function getNames()
    {
        return [ $this->languageCode => $this->name ];
    }

    /**
     * Returns the name computed from the name schema in the given language.
     * If no language is given the name in initial language of the version if present, otherwise null.
     *
     * @param string $languageCode (deprecated)
     *
     * @return string
     */
    public function getName($languageCode = null)
    {
        return $this->name;
    }
}
