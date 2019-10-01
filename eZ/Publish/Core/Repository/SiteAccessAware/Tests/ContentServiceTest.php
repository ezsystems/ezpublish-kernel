<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\ContentService as APIService;
use eZ\Publish\API\Repository\Values\Content\ContentDraftList;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\SiteAccessAware\ContentService;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;

class ContentServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return ContentService::class;
    }

    public function providerForPassTroughMethods()
    {
        $contentInfo = new ContentInfo();
        $versionInfo = new VersionInfo();
        $contentCreateStruct = new ContentCreateStruct();
        $contentUpdateStruct = new ContentUpdateStruct();
        $contentMetaStruct = new ContentMetadataUpdateStruct();
        $locationCreateStruct = new LocationCreateStruct();
        $user = new User();
        $contentType = new ContentType();

        // string $method, array $arguments, bool $return = true
        return [
            ['loadContentInfo', [42]],
            ['loadContentInfoList', [[42]], [$contentInfo]],

            ['loadContentInfoByRemoteId', ['f348tj4gorgji4']],

            ['loadVersionInfo', [$contentInfo]],
            ['loadVersionInfo', [$contentInfo, 3]],

            ['loadVersionInfoById', [42]],
            ['loadVersionInfoById', [42, 3]],

            ['createContent', [$contentCreateStruct]],
            ['createContent', [$contentCreateStruct, [44]]],

            ['updateContentMetadata', [$contentInfo, $contentMetaStruct]],

            ['deleteContent', [$contentInfo]],

            ['createContentDraft', [$contentInfo]],
            ['createContentDraft', [$contentInfo, $versionInfo]],
            ['createContentDraft', [$contentInfo, $versionInfo, $user]],

            ['countContentDrafts', []],
            ['countContentDrafts', [$user]],

            ['loadContentDrafts', []],
            ['loadContentDrafts', [$user]],

            ['loadContentDraftList', [], new ContentDraftList()],
            ['loadContentDraftList', [$user], new ContentDraftList()],
            ['loadContentDraftList', [$user, 1], new ContentDraftList()],
            ['loadContentDraftList', [$user, 1, 25], new ContentDraftList()],

            ['updateContent', [$versionInfo, $contentUpdateStruct]],

            ['publishVersion', [$versionInfo]],

            ['deleteVersion', [$versionInfo]],

            ['loadVersions', [$contentInfo]],

            ['copyContent', [$contentInfo, $locationCreateStruct]],
            ['copyContent', [$contentInfo, $locationCreateStruct, $versionInfo]],

            ['loadRelations', [$versionInfo]],

            ['countReverseRelations', [$contentInfo]],

            ['loadReverseRelations', [$contentInfo]],

            ['addRelation', [$versionInfo, $contentInfo]],

            ['deleteRelation', [$versionInfo, $contentInfo]],

            ['removeTranslation', [$contentInfo, 'eng-GB']],

            ['deleteTranslation', [$contentInfo, 'eng-GB']],

            ['deleteTranslationFromDraft', [$versionInfo, 'eng-GB']],

            ['newContentCreateStruct', [$contentType, 'eng-GB']],
            ['newContentMetadataUpdateStruct', []],
            ['newContentUpdateStruct', []],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $contentInfo = new ContentInfo();
        $versionInfo = new VersionInfo();

        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [
            ['loadContentByContentInfo', [$contentInfo], true, 1],
            ['loadContentByContentInfo', [$contentInfo, self::LANG_ARG, 4, false], true, 1],

            ['loadContentByVersionInfo', [$versionInfo], true, 1],
            ['loadContentByVersionInfo', [$versionInfo, self::LANG_ARG, false], true, 1],

            ['loadContent', [42], true, 1],
            ['loadContent', [42, self::LANG_ARG, 4, false], true, 1],

            ['loadContentByRemoteId', ['f348tj4gorgji4'], true, 1],
            ['loadContentByRemoteId', ['f348tj4gorgji4', self::LANG_ARG, 4, false], true, 1],
        ];
    }
}
