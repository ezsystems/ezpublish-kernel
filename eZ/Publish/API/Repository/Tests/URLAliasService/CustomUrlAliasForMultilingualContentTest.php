<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\URLAliasService;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Tests\BaseTest;

final class CustomUrlAliasForMultilingualContentTest extends BaseTest
{
    /**
     * @covers \eZ\Publish\API\Repository\ContentService::publishVersion
     * @covers \eZ\Publish\API\Repository\URLAliasService::createUrlAlias
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateCustomUrlAliasWithTheSamePathThrowsException(): void
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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Argument \'$path\' is invalid: Path \'Contact\' already exists for the given language'
        );
        // attempt to create custom alias for German translation while a system one
        // for a different translation already exists
        $urlAliasService->createUrlAlias(
            $locationService->loadLocation(
                $contactFolder->contentInfo->mainLocationId
            ),
            'Contact',
            $language,
            true, // forwarding
            true // always available
        );
    }
}
