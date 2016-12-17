<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Issue https://jira.ez.no/browse/EZP-26367.
 * @group regression
 * @group ezp26367
 * @group cache
 * @group cache-invalidation
 * @group cache-spi
 */
class EZP26367UrlAliasHistoryRedirectLoopTest extends BaseTest
{
    public function testReverseLookupReturnsHistoryAlias()
    {
        $contentService = $this->getRepository()->getContentService();
        $contentTypeService = $this->getRepository()->getContentTypeService();
        $locationService = $this->getRepository()->getLocationService();
        $urlAliasService = $this->getRepository()->getURLAliasService();

        // Create container for articles

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');

        $contentCreateStruct->setField('name', 'Articles');
        $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $folder = $contentService->publishVersion($draft->versionInfo);

        // Create one article in the container

        $contentType = $contentTypeService->loadContentTypeByIdentifier('article');
        $locationCreateStruct = $locationService->newLocationCreateStruct(
            $folder->contentInfo->mainLocationId
        );
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');

        $contentCreateStruct->setField('title', 'Article');
        $contentCreateStruct->setField(
            'intro',
            <<< DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" version="5.0-variant ezpublish-1.0">
    <para>Cache invalidation in eZ</para>
</section>
DOCBOOK
        );
        $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $article = $contentService->publishVersion($draft->versionInfo);

        // Rename article container

        $draft = $contentService->createContentDraft($folder->contentInfo);
        $contentUpdateStruct = $contentService->newContentUpdateStruct();

        $contentUpdateStruct->setField('name', 'Articles-UPDATED');
        $draft = $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $historyPath = '/Articles/Article';
        $activePath = '/Articles-UPDATED/Article';

        // Lookup history first to warm-up URL alias object lookup cache by ID

        $urlAliasHistorized = $urlAliasService->lookup($historyPath);

        $this->assertEquals($historyPath, $urlAliasHistorized->path);
        $this->assertTrue($urlAliasHistorized->isHistory);

        // Reverse lookup once to warm-up URL alias ID cache by Location ID

        $urlAlias = $urlAliasService->reverseLookup(
            $locationService->loadLocation($article->contentInfo->mainLocationId)
        );

        $this->assertEquals($activePath, $urlAlias->path);
        $this->assertFalse($urlAlias->isHistory);

        // Reverse lookup again to trigger return of URL alias object lookup cache by ID,
        // through URL alias ID cache by Location ID

        $urlAlias = $urlAliasService->reverseLookup(
            $locationService->loadLocation($article->contentInfo->mainLocationId)
        );

        $this->assertEquals($activePath, $urlAlias->path);
        $this->assertFalse($urlAlias->isHistory);
    }

    public function testLookupHistoryUrlReturnsActiveAlias()
    {
        $contentService = $this->getRepository()->getContentService();
        $contentTypeService = $this->getRepository()->getContentTypeService();
        $locationService = $this->getRepository()->getLocationService();
        $urlAliasService = $this->getRepository()->getURLAliasService();

        // Create container for articles

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');

        $contentCreateStruct->setField('name', 'Articles');
        $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $folder = $contentService->publishVersion($draft->versionInfo);

        // Create one article in the container

        $contentType = $contentTypeService->loadContentTypeByIdentifier('article');
        $locationCreateStruct = $locationService->newLocationCreateStruct(
            $folder->contentInfo->mainLocationId
        );
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');

        $contentCreateStruct->setField('title', 'Article');
        $contentCreateStruct->setField(
            'intro',
            <<< DOCBOOK
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" version="5.0-variant ezpublish-1.0">
    <para>Cache invalidation in eZ</para>
</section>
DOCBOOK
        );
        $draft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $article = $contentService->publishVersion($draft->versionInfo);

        // Rename article container

        $draft = $contentService->createContentDraft($folder->contentInfo);
        $contentUpdateStruct = $contentService->newContentUpdateStruct();

        $contentUpdateStruct->setField('name', 'Articles-UPDATED');
        $draft = $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $historyPath = '/Articles/Article';
        $activePath = '/Articles-UPDATED/Article';

        // Reverse lookup to warm-up URL alias ID cache by Location ID

        $urlAlias = $urlAliasService->reverseLookup(
            $locationService->loadLocation($article->contentInfo->mainLocationId)
        );

        $this->assertEquals($activePath, $urlAlias->path);
        $this->assertFalse($urlAlias->isHistory);

        $urlAlias = $urlAliasService->reverseLookup(
            $locationService->loadLocation($article->contentInfo->mainLocationId)
        );

        $this->assertEquals($activePath, $urlAlias->path);
        $this->assertFalse($urlAlias->isHistory);

        // Lookup history URL one to warm-up URL alias ID cache by URL

        $urlAliasHistorized = $urlAliasService->lookup($historyPath);

        $this->assertEquals($historyPath, $urlAliasHistorized->path);
        $this->assertTrue($urlAliasHistorized->isHistory);

        // Lookup history URL again to trigger return of URL alias object reverse lookup cache by ID,
        // through URL alias ID cache by URL

        $urlAliasHistorized = $urlAliasService->lookup($historyPath);

        $this->assertEquals($historyPath, $urlAliasHistorized->path);
        $this->assertTrue($urlAliasHistorized->isHistory);
    }
}
