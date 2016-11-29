<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Issue https://jira.ez.no/browse/EZP-26327.
 * @group ezp26327
 */
class EZP26327UrlAliasHistorizationTest extends BaseTest
{
    public function testHistorization()
    {
        $contentService = $this->getRepository()->getContentService();
        $contentTypeService = $this->getRepository()->getContentTypeService();
        $locationService = $this->getRepository()->getLocationService();
        $urlAliasService = $this->getRepository()->getURLAliasService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');

        $contentCreateStruct->setField('name', 'name-gb', 'eng-GB');
        $contentCreateStruct->setField('name', 'name-us', 'eng-US');

        $draft = $contentService->createContent(
            $contentCreateStruct,
            [$locationCreateStruct]
        );
        $content = $contentService->publishVersion($draft->versionInfo);

        // Warmup cache
        $urlAliasService->lookup('/name-gb');
        $urlAliasService->lookup('/name-us');

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField('name', 'name-gb', 'eng-US');
        $draft = $contentService->createContentDraft($content->contentInfo);
        $draft = $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);
        $contentService->publishVersion($draft->versionInfo);

        $activeAlias = $urlAliasService->lookup('/name-gb');
        $historyAlias = $urlAliasService->lookup('/name-us');

        $this->assertFalse($activeAlias->isHistory);
        $this->assertTrue($historyAlias->isHistory);
    }
}
