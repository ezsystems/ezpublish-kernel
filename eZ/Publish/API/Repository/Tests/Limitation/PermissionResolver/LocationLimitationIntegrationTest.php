<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Limitation\PermissionResolver;

use eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation;
use eZ\Publish\SPI\Limitation\Target\Version;

class LocationLimitationIntegrationTest extends BaseLimitationIntegrationTest
{
    private const LOCATION_ID = 2;

    public function providerForCanUserEditOrPublishContent(): array
    {
        $limitationRoot = new LocationLimitation();
        $limitationRoot->limitationValues = [self::LOCATION_ID];

        return [
            [[$limitationRoot], true],
        ];
    }

    /**
     * @dataProvider providerForCanUserEditOrPublishContent
     *
     * @param array $limitations
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserEditContent(array $limitations, bool $expectedResult): void
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation(2);

        $this->loginAsEditorUserWithLimitations('content', 'edit', $limitations);

        $this->assertCanUser(
            $expectedResult,
            'content',
            'edit',
            $limitations,
            $location->contentInfo,
            [$location]
        );

        $this->assertCanUser(
            $expectedResult,
            'content',
            'edit',
            $limitations,
            $location->contentInfo,
            [$location, new Version(['allLanguageCodesList' => 'eng-GB'])]
        );
    }
}
