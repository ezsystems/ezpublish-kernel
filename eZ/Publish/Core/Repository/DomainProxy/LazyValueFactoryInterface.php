<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\DomainProxy;

use eZ\Publish\API\Repository\Values\Content\SectionLazyValue;
use eZ\Publish\Core\Repository\Values\Content\LanguageProxy;
use eZ\Publish\Core\Repository\Values\Content\SectionProxy;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeProxy;

interface LazyValueFactoryInterface
{
    //public function buildContentTypeProxy(int $contentTypeId): ContentTypeProxy;

    public function createSectionLazyValue(int $sectionId): SectionLazyValue;

    //public function buildLanguageProxy(string $languageCode): LanguageProxy;
}
