<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\URLAliasService;

use eZ\Publish\API\Repository\Tests\BaseTest;
use function sprintf;

class SystemURLAliasPublishingTest extends BaseTest
{
    /**
     * @covers \eZ\Publish\API\Repository\ContentService::publishVersion
     * @covers \eZ\Publish\API\Repository\URLAliasService::createUrlAlias
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishingUrlAliasesWithCustomForTheSamePathExists(): void
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $folder = $this->createFolder(['eng-GB' => 'Contact', 'ger-DE' => 'Kontakt'], 2);
        $folderLocation = $locationService->loadLocation($folder->contentInfo->mainLocationId);

        // sanity check
        $this->assertLookupUrlAliasIsCorrect(
            $folderLocation->id,
            '/Contact',
            'eng-GB',
            false
        );
        $this->assertLookupUrlAliasIsCorrect(
            $folderLocation->id,
            '/Kontakt',
            'ger-DE',
            false
        );

        // create custom forwarding (redirecting) URL alias and lookup both system ones and that alias
        $urlAliasService->createUrlAlias($folderLocation, 'Contact', 'ger-DE', true, true);

        // publish new Version of the Content to trigger publishing system URL aliases
        $contentService->publishVersion(
            $contentService->createContentDraft($folder->contentInfo)->getVersionInfo()
        );

        $this->assertLookupUrlAliasIsCorrect(
            $folderLocation->id,
            '/Contact',
            'eng-GB',
            false
        );
        $this->assertLookupUrlAliasIsCorrect(
            $folderLocation->id,
            '/Kontakt',
            'ger-DE',
            false
        );
        $this->assertLookupUrlAliasIsCorrect(
            $folderLocation->id,
            '/Contact',
            'ger-DE',
            true
        );
    }

    /**
     * @param int $locationId
     * @param string $expectedPath
     * @param string $lookupLanguageCode
     * @param bool $expectedIsCustom
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function assertLookupUrlAliasIsCorrect(
        int $locationId,
        string $expectedPath,
        string $lookupLanguageCode,
        bool $expectedIsCustom
    ): void {
        $repository = $this->getRepository(false);
        $urlAliasService = $repository->getURLAliasService();

        $urlAlias = $urlAliasService->lookup($expectedPath, $lookupLanguageCode);

        $expectationFailedMsgPrefix = sprintf(
            'Expected URL Alias "%s" (%s)',
            $expectedPath,
            $lookupLanguageCode
        );

        self::assertEquals(
            $locationId,
            $urlAlias->destination,
            "{$expectationFailedMsgPrefix} to have different destination"
        );
        self::assertEquals(
            $expectedPath,
            $urlAlias->path,
            "{$expectationFailedMsgPrefix} to have path"
        );
        self::assertEquals(
            [$lookupLanguageCode],
            $urlAlias->languageCodes,
            "{$expectationFailedMsgPrefix} to have different language codes"
        );
        self::assertEquals(
            $expectedIsCustom,
            $urlAlias->isCustom,
            "{$expectationFailedMsgPrefix} to have different 'isCustom' flag"
        );
    }
}
