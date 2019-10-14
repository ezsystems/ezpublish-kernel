<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * @internal
 */
interface ProxyFactoryInterface
{
    public function createContentProxy(int $contentId, array $prioritizedLanguages = Language::ALL, bool $useAlwaysAvailable = true): Content;

    public function createContentInfoProxy(int $contentId): ContentInfo;

    public function createContentTypeProxy(int $contentTypeId, array $prioritizedLanguages = Language::ALL): ContentType;

    public function createContentTypeGroupProxy(int $contentTypeGroupId, array $prioritizedLanguages = Language::ALL): ContentTypeGroup;

    public function createContentTypeGroupProxyList(array $contentTypeGroupIds, array $prioritizedLanguages = Language::ALL): array;

    public function createLanguageProxy(string $languageCode): Language;

    public function createLocationProxy(int $locationId, array $prioritizedLanguages = Language::ALL): Location;

    public function createSectionProxy(int $sectionId): Section;

    public function createUserProxy(int $userId, array $prioritizedLanguages = Language::ALL): User;
}
