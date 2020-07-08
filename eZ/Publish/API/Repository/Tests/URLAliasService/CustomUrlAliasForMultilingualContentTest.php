<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\URLAliasService;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use function sprintf;

final class CustomUrlAliasForMultilingualContentTest extends BaseTest
{
    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateCustomUrlAliasWithTheSamePath(): void
    {
        $repository = $this->getRepository();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();
        $language = 'ger-DE';

        $names = [
            'eng-GB' => 'Contact',
            'ger-DE' => 'Kontakt',
            'eng-US' => 'Contact',
        ];
        $contactFolder = $this->createFolder(
            $names,
            2,
            false // not always available, so the created content behaves the same as "article"
        );

        $systemUrlAliases = $this->loadExpectedSystemUrlAliases($urlAliasService, $names);

        // create custom alias for German site
        $actualCustomUrlAlias = $urlAliasService->createUrlAlias(
            $locationService->loadLocation(
                $contactFolder->contentInfo->mainLocationId
            ),
            'Contact',
            $language,
            true, // forwarding
            true // always available
        );
        $expectedLocationId = $contactFolder->contentInfo->mainLocationId;

        $this->assertPropertiesCorrect(
            [
                'forward' => true,
                'isCustom' => true,
                'destination' => $expectedLocationId,
            ],
            $actualCustomUrlAlias
        );
        self::assertEquals($urlAliasService->lookup('Contact', 'ger-DE'), $actualCustomUrlAlias);
        // check the system URL Aliases are still correct
        foreach ($names as $_languageCode => $name) {
            $this->assertSystemUrlForTranslationIsCorrect(
                $urlAliasService,
                $systemUrlAliases[$_languageCode],
                $name,
                $_languageCode
            );
        }
    }

    protected function loadExpectedSystemUrlAliases(
        URLAliasService $urlAliasService,
        array $names
    ): array {
        $systemUrlAliases = [];
        foreach ($names as $_languageCode => $name) {
            try {
                $systemUrlAliases[$_languageCode] = $urlAliasService->lookup(
                    $name,
                    $_languageCode
                );
            } catch (InvalidArgumentException | NotFoundException $e) {
                self::fail(
                    sprintf('Failed to load URL Alias "%s" (%s): %s', $name, $_languageCode, $e)
                );
            }
        }

        return $systemUrlAliases;
    }

    protected function assertSystemUrlForTranslationIsCorrect(
        URLAliasService $urlAliasService,
        URLAlias $expectedSystemUrlAlias,
        string $name,
        string $_languageCode
    ): void {
        try {
            self::assertEquals(
                $expectedSystemUrlAlias,
                $urlAliasService->lookup(
                    $name,
                    $_languageCode
                ),
                "System URL alias '{$name}' for '{$_languageCode}' translation is not as expected"
            );
        } catch (InvalidArgumentException | NotFoundException $e) {
            self::fail(
                sprintf(
                    "Failed to retrieve System URL alias '%s' for '%s' translation: %s\n%s",
                    $name,
                    $_languageCode,
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );
        }
    }
}
