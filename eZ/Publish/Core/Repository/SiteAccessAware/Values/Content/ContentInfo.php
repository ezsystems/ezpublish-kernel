<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo;

/**
 * @property-read string $name Name of the content in a specific language (@see VersionInfo::$languageCode)
 * @property-read string $languageCode The language code of the content name (@see VersionInfo::$name)
 */
class ContentInfo extends APIContentInfo
{
    /**
     * The name in a preferred language of current SiteAccess (with a fallback to main language).
     *
     * @var string
     */
    protected $name;

    /**
     * The language code of Content name.
     *
     * @var string
     */
    protected $languageCode;
}