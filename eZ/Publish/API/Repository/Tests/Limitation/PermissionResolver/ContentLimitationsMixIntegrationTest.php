<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Limitation\PermissionResolver;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * Test mix of chosen core Content Limitations.
 */
class ContentLimitationsMixIntegrationTest extends BaseLimitationIntegrationTest
{
    const LIMITATION_VALUES = 'limitationValues';

    /**
     * Provides lists of:.
     *
     * <code>[string $module, string $function, array $limitations, bool $expectedResult]</code>
     *
     * This provider also checks if all registered Limitations are used.
     */
    public function providerForCanUser(): array
    {
        $commonLimitations = $this->getCommonLimitations();
        $contentCreateLimitations = array_merge(
            $commonLimitations,
            [
                new Limitation\ParentContentTypeLimitation([self::LIMITATION_VALUES => [1]]),
                new Limitation\ParentDepthLimitation([self::LIMITATION_VALUES => [2]]),
                new Limitation\LanguageLimitation([self::LIMITATION_VALUES => ['eng-US']]),
            ]
        );

        $contentEditLimitations = array_merge(
            $commonLimitations,
            [
                new Limitation\ObjectStateLimitation(
                    [self::LIMITATION_VALUES => [1, 2]]
                ),
                new Limitation\LanguageLimitation([self::LIMITATION_VALUES => ['eng-US']]),
            ]
        );

        $contentVersionReadLimitations = array_merge(
            $commonLimitations,
            [
                new Limitation\StatusLimitation(
                    [self::LIMITATION_VALUES => [VersionInfo::STATUS_PUBLISHED]]
                ),
            ]
        );

        return [
            ['content', 'create', $contentCreateLimitations, true],
            ['content', 'edit', $contentEditLimitations, true],
            ['content', 'publish', $contentEditLimitations, true],
            ['content', 'versionread', $contentVersionReadLimitations, true],
        ];
    }

    /**
     * @dataProvider providerForCanUser
     *
     * @param string $module
     * @param string $function
     * @param array $limitations
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUser(
        string $module,
        string $function,
        array $limitations,
        bool $expectedResult
    ): void {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $folder = $this->createFolder(['eng-US' => 'Folder'], 2);
        $location = $locationService->loadLocation($folder->contentInfo->mainLocationId);

        $this->loginAsEditorUserWithLimitations($module, $function, $limitations);

        $this->assertCanUser(
            $expectedResult,
            $module,
            $function,
            $limitations,
            $folder,
            [$location]
        );
    }

    /**
     * Get a list of Limitations common to all test cases.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    private function getCommonLimitations(): array
    {
        return [
            new Limitation\ContentTypeLimitation([self::LIMITATION_VALUES => [1]]),
            new Limitation\SectionLimitation([self::LIMITATION_VALUES => [1]]),
            new Limitation\SubtreeLimitation([self::LIMITATION_VALUES => ['/1/2']]),
        ];
    }
}
