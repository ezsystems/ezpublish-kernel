<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Parallel;

use eZ\Publish\Core\Base\Exceptions\BadStateException;

final class ContentServiceTest extends BaseParallelTestCase
{
    public function testPublishMultipleVersions(): void
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $content = $this->createFolder(
            [
                'eng-US' => 'Content',
            ],
            $this->generateId('location', 2)
        );

        $version1 = $contentService->createContentDraft($content->contentInfo, $content->versionInfo);
        $version2 = $contentService->createContentDraft($content->contentInfo, $content->versionInfo);

        $processList = new ParallelProcessList();
        $this->addParallelProcess($processList, function () use ($version1 , $contentService) {
            try {
                $contentService->publishVersion($version1->versionInfo);
            } catch (BadStateException $e) {
            }
        });

        $this->addParallelProcess($processList, function () use ($version2 , $contentService) {
            try {
                $contentService->publishVersion($version2->versionInfo);
            } catch (BadStateException $e) {
            }
        });

        $this->runParallelProcesses($processList);

        $version1 = $contentService->loadVersionInfo($version1->contentInfo, 2);
        $version2 = $contentService->loadVersionInfo($version2->contentInfo, 3);

        $this->assertTrue(
            $version1->isPublished() && $version2->isDraft() || $version1->isDraft() && $version2->isPublished(),
            'One of the versions should be published and the other should be draft');
    }
}
